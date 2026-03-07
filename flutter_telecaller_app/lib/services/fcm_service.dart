import 'package:firebase_messaging/firebase_messaging.dart';
import 'package:telecaller_crm/services/api_service.dart';
import 'package:telecaller_crm/services/storage_service.dart';
import 'package:telecaller_crm/config/api_config.dart';
import 'package:flutter/foundation.dart';

class FcmService {
  static final FcmService _instance = FcmService._internal();
  factory FcmService() => _instance;
  FcmService._internal();

  final FirebaseMessaging _messaging = FirebaseMessaging.instance;
  final ApiService _apiService = ApiService();

  Future<void> initialize() async {
    final settings = await _messaging.requestPermission(
      alert: true,
      badge: true,
      sound: true,
    );

    if (settings.authorizationStatus == AuthorizationStatus.authorized ||
        settings.authorizationStatus == AuthorizationStatus.provisional) {
      final token = await _messaging.getToken();
      if (token != null) {
        await _sendTokenToServer(token);
      }

      _messaging.onTokenRefresh.listen(_sendTokenToServer);

      FirebaseMessaging.onMessage.listen((RemoteMessage message) {
        debugPrint('FCM foreground message: ${message.notification?.title}');
      });
    }
  }

  Future<void> _sendTokenToServer(String fcmToken) async {
    final authToken = await StorageService.getToken();
    if (authToken == null || authToken.isEmpty) return;

    try {
      await _apiService.post(
        '${_baseUrl}/fcm-subscription',
        data: {
          'fcm_token': fcmToken,
          'device_type': 'android',
        },
      );
    } catch (e) {
      debugPrint('Failed to send FCM token to server: $e');
    }
  }

  String get _baseUrl => ApiConfig.baseUrl;
}
