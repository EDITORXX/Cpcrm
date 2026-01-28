import 'package:flutter/foundation.dart';
import 'package:telecaller_crm/services/auth_service.dart';
import 'package:telecaller_crm/models/user_model.dart';

class AuthProvider with ChangeNotifier {
  final AuthService _authService = AuthService();
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

  Future<void> logout() async {
    _isLoading = true;
    notifyListeners();

    try {
      await _authService.logout();
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

