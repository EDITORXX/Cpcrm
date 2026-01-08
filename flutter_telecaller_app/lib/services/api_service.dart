import 'package:dio/dio.dart';
import 'package:telecaller_crm/services/storage_service.dart';
import 'package:telecaller_crm/config/api_config.dart';
import 'package:telecaller_crm/models/api_response_model.dart';

class ApiService {
  late Dio _dio;

  ApiService() {
    _dio = Dio(
      BaseOptions(
        baseUrl: ApiConfig.baseUrl,
        connectTimeout: const Duration(seconds: 30),
        receiveTimeout: const Duration(seconds: 30),
        headers: {
          'Content-Type': 'application/json',
          'Accept': 'application/json',
        },
      ),
    );

    _dio.interceptors.add(
      InterceptorsWrapper(
        onRequest: (options, handler) async {
          final token = await StorageService.getToken();
          if (token != null) {
            options.headers['Authorization'] = 'Bearer $token';
          }
          return handler.next(options);
        },
        onResponse: (response, handler) {
          return handler.next(response);
        },
        onError: (error, handler) {
          if (error.response?.statusCode == 401) {
            // Handle unauthorized - clear token and redirect to login
            StorageService.removeToken();
            StorageService.removeUser();
          }
          return handler.next(error);
        },
      ),
    );
  }

  Future<ApiResponse<T>> get<T>(
    String endpoint, {
    Map<String, dynamic>? queryParameters,
    T Function(dynamic)? fromJson,
  }) async {
    try {
      final response = await _dio.get(
        endpoint,
        queryParameters: queryParameters,
      );

      if (response.statusCode == 200) {
        if (fromJson != null && response.data['data'] != null) {
          return ApiResponse.fromJson(response.data, fromJson);
        }
        return ApiResponse.success(response.data as T);
      } else {
        return ApiResponse.error(
          response.data['message'] ?? 'An error occurred',
          statusCode: response.statusCode,
        );
      }
    } on DioException catch (e) {
      return _handleError(e);
    } catch (e) {
      return ApiResponse.error('Unexpected error: $e');
    }
  }

  Future<ApiResponse<T>> post<T>(
    String endpoint, {
    dynamic data,
    T Function(dynamic)? fromJson,
    Options? options,
  }) async {
    try {
      final response = await _dio.post(
        endpoint,
        data: data,
        options: options,
      );

      if (response.statusCode == 200 || response.statusCode == 201) {
        if (fromJson != null && response.data['data'] != null) {
          return ApiResponse.fromJson(response.data, fromJson);
        }
        return ApiResponse.success(response.data as T);
      } else {
        return ApiResponse.error(
          response.data['message'] ?? 'An error occurred',
          statusCode: response.statusCode,
        );
      }
    } on DioException catch (e) {
      return _handleError(e);
    } catch (e) {
      return ApiResponse.error('Unexpected error: $e');
    }
  }

  Future<ApiResponse<T>> put<T>(
    String endpoint, {
    dynamic data,
    T Function(dynamic)? fromJson,
  }) async {
    try {
      final response = await _dio.put(endpoint, data: data);

      if (response.statusCode == 200) {
        if (fromJson != null && response.data['data'] != null) {
          return ApiResponse.fromJson(response.data, fromJson);
        }
        return ApiResponse.success(response.data as T);
      } else {
        return ApiResponse.error(
          response.data['message'] ?? 'An error occurred',
          statusCode: response.statusCode,
        );
      }
    } on DioException catch (e) {
      return _handleError(e);
    } catch (e) {
      return ApiResponse.error('Unexpected error: $e');
    }
  }

  Future<Response> postFormData(
    String endpoint, {
    required FormData formData,
  }) async {
    return await _dio.post(
      endpoint,
      data: formData,
      options: Options(
        contentType: 'multipart/form-data',
      ),
    );
  }

  ApiResponse<T> _handleError<T>(DioException error) {
    if (error.response != null) {
      final statusCode = error.response!.statusCode;
      final message = error.response!.data['message'] ??
          error.response!.data['error'] ??
          'An error occurred';

      return ApiResponse.error(message, statusCode: statusCode);
    } else if (error.type == DioExceptionType.connectionTimeout ||
        error.type == DioExceptionType.receiveTimeout) {
      return ApiResponse.error('Connection timeout. Please try again.');
    } else if (error.type == DioExceptionType.connectionError) {
      return ApiResponse.error('No internet connection. Please check your network.');
    } else {
      return ApiResponse.error('An unexpected error occurred: ${error.message}');
    }
  }
}

