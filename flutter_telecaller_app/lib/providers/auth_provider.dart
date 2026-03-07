import 'package:flutter/foundation.dart';
import 'package:telecaller_crm/services/auth_service.dart';
import 'package:telecaller_crm/services/firebase_auth_service.dart';
import 'package:telecaller_crm/services/api_service.dart';
import 'package:telecaller_crm/services/storage_service.dart';
import 'package:telecaller_crm/config/api_config.dart';
import 'package:telecaller_crm/models/user_model.dart';

class AuthProvider with ChangeNotifier {
  final AuthService _authService = AuthService();
  final FirebaseAuthService _firebaseAuthService = FirebaseAuthService();
  final ApiService _apiService = ApiService();
  UserModel? _user;
  bool _isLoading = false;
  String? _error;

  UserModel? get user => _user;
  bool get isLoading => _isLoading;
  String? get error => _error;
  bool get isAuthenticated => _user != null;
  
  // Role-based getters
  bool get isAdmin => _user?.isAdmin ?? false;
  bool get isCrm => _user?.isCrm ?? false;
  bool get isSalesManager => _user?.isSalesManager ?? false;
  bool get isSalesExecutive => _user?.isSalesExecutive ?? false;
  bool get isTelecaller => _user?.isTelecaller ?? false;
  bool get isHrManager => _user?.isHrManager ?? false;
  bool get isFinanceManager => _user?.isFinanceManager ?? false;
  
  String get userRole => _user?.role ?? 'sales_executive';
  String get userRoleName => _user?.roleName ?? _user?.role ?? 'Sales Executive';

  Future<bool> checkAuth() async {
    _isLoading = true;
    notifyListeners();

    try {
      final isAuth = await _authService.isAuthenticated();
      if (isAuth) {
        final response = await _authService.getCurrentUser();
        if (response.success && response.data != null) {
          _user = response.data;
          _error = null;
        } else {
          _error = response.message;
        }
      }
    } catch (e) {
      _error = e.toString();
    } finally {
      _isLoading = false;
      notifyListeners();
    }

    return isAuthenticated;
  }

  Future<bool> login(String email, String password) async {
    _isLoading = true;
    _error = null;
    notifyListeners();

    try {
      final response = await _authService.login(email, password);
      if (response.success && response.data != null) {
        final userData = response.data!['user'];
        if (userData != null) {
          _user = UserModel.fromJson(userData);
          _error = null;
          return true;
        }
      }
      _error = response.message ?? 'Login failed';
      return false;
    } catch (e) {
      _error = e.toString();
      return false;
    } finally {
      _isLoading = false;
      notifyListeners();
    }
  }

  Future<bool> loginWithGoogle() async {
    _isLoading = true;
    _error = null;
    notifyListeners();

    try {
      final idToken = await _firebaseAuthService.signInWithGoogle();
      if (idToken == null) {
        _error = 'Google Sign-In cancelled';
        return false;
      }

      final response = await _apiService.post<Map<String, dynamic>>(
        ApiConfig.loginFirebase,
        data: {'id_token': idToken},
      );

      if (response.success && response.data != null) {
        final token = response.data!['token'];
        final userData = response.data!['user'];
        if (token != null) {
          await StorageService.saveToken(token);
          if (userData != null) {
            _user = UserModel.fromJson(userData);
            _error = null;
            return true;
          }
        }
      }
      _error = response.message ?? 'Google Sign-In failed';
      await _firebaseAuthService.signOut();
      return false;
    } catch (e) {
      _error = e.toString();
      await _firebaseAuthService.signOut();
      return false;
    } finally {
      _isLoading = false;
      notifyListeners();
    }
  }

  Future<void> logout() async {
    _isLoading = true;
    notifyListeners();

    try {
      await _authService.logout();
      await _firebaseAuthService.signOut();
      _user = null;
      _error = null;
    } catch (e) {
      _error = e.toString();
    } finally {
      _isLoading = false;
      notifyListeners();
    }
  }

  void clearError() {
    _error = null;
    notifyListeners();
  }
}

