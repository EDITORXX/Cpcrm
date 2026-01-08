import 'package:telecaller_crm/services/api_service.dart';
import 'package:telecaller_crm/services/storage_service.dart';
import 'package:telecaller_crm/config/api_config.dart';
import 'package:telecaller_crm/models/user_model.dart';
import 'package:telecaller_crm/models/api_response_model.dart';

class AuthService {
  final ApiService _apiService = ApiService();

  Future<ApiResponse<Map<String, dynamic>>> login(
    String email,
    String password,
  ) async {
    final response = await _apiService.post<Map<String, dynamic>>(
      ApiConfig.login,
      data: {
        'email': email,
        'password': password,
      },
    );

    if (response.success && response.data != null) {
      final token = response.data!['token'];
      final userData = response.data!['user'];

      if (token != null) {
        await StorageService.saveToken(token);
        if (userData != null) {
          await StorageService.saveUser(userData.toString());
        }
      }
    }

    return response;
  }

  Future<ApiResponse<void>> logout() async {
    try {
      await _apiService.post(ApiConfig.logout);
      await StorageService.clearAll();
      return ApiResponse.success(null, message: 'Logged out successfully');
    } catch (e) {
      // Even if API call fails, clear local storage
      await StorageService.clearAll();
      return ApiResponse.error('Logout failed: $e');
    }
  }

  Future<ApiResponse<UserModel>> getCurrentUser() async {
    final response = await _apiService.get<Map<String, dynamic>>(
      ApiConfig.whoami,
      fromJson: (data) => data as Map<String, dynamic>,
    );

    if (response.success && response.data != null) {
      final user = UserModel.fromJson(response.data!);
      return ApiResponse.success(user);
    }

    return ApiResponse.error(response.message ?? 'Failed to get user');
  }

  Future<bool> isAuthenticated() async {
    final token = await StorageService.getToken();
    return token != null && token.isNotEmpty;
  }

  Future<String?> getToken() async {
    return await StorageService.getToken();
  }
}

