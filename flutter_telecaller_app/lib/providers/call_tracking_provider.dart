import 'package:flutter/foundation.dart';
import 'package:telecaller_crm/services/api_service.dart';
import 'package:telecaller_crm/config/api_config.dart';
import 'package:telecaller_crm/models/call_log_model.dart';

class CallTrackingProvider with ChangeNotifier {
  final ApiService _apiService = ApiService();
  List<CallLogModel> _callLogs = [];
  bool _isLoading = false;
  String? _error;
  
  // Statistics
  int _todayTotalCalls = 0;
  int _todayIncomingCalls = 0;
  int _todayOutgoingCalls = 0;
  int _todayTotalTalkingTime = 0; // in seconds
  double _averageDuration = 0;

  List<CallLogModel> get callLogs => _callLogs;
  bool get isLoading => _isLoading;
  String? get error => _error;
  int get todayTotalCalls => _todayTotalCalls;
  int get todayIncomingCalls => _todayIncomingCalls;
  int get todayOutgoingCalls => _todayOutgoingCalls;
  int get todayTotalTalkingTime => _todayTotalTalkingTime;
  double get averageDuration => _averageDuration;

  String get formattedTalkingTime {
    final hours = _todayTotalTalkingTime ~/ 3600;
    final minutes = (_todayTotalTalkingTime % 3600) ~/ 60;
    final seconds = _todayTotalTalkingTime % 60;
    
    if (hours > 0) {
      return '${hours}h ${minutes}m ${seconds}s';
    } else if (minutes > 0) {
      return '${minutes}m ${seconds}s';
    } else {
      return '${seconds}s';
    }
  }

  Future<void> loadCallStatistics() async {
    _isLoading = true;
    _error = null;
    notifyListeners();

    try {
      final response = await _apiService.get<Map<String, dynamic>>(
        ApiConfig.callStatistics,
        fromJson: (data) => data as Map<String, dynamic>,
      );

      if (response.success && response.data != null) {
        final today = response.data!['today'] as Map<String, dynamic>?;
        if (today != null) {
          _todayTotalCalls = today['total_calls'] ?? 0;
          _todayIncomingCalls = today['incoming_calls'] ?? 0;
          _todayOutgoingCalls = today['outgoing_calls'] ?? 0;
          _todayTotalTalkingTime = today['total_talking_time'] ?? 0;
          _averageDuration = (today['average_duration'] ?? 0).toDouble();
        }
        _error = null;
      } else {
        _error = response.message ?? 'Failed to load statistics';
      }
    } catch (e) {
      _error = e.toString();
    } finally {
      _isLoading = false;
      notifyListeners();
    }
  }

  Future<void> loadCallLogs({DateTime? from, DateTime? to}) async {
    _isLoading = true;
    _error = null;
    notifyListeners();

    try {
      final queryParams = <String, dynamic>{};
      if (from != null) {
        queryParams['from_date'] = from.toIso8601String();
      }
      if (to != null) {
        queryParams['to_date'] = to.toIso8601String();
      }

      final response = await _apiService.get<List<dynamic>>(
        ApiConfig.callLogs,
        queryParameters: queryParams,
        fromJson: (data) => data as List,
      );

      if (response.success && response.data != null) {
        _callLogs = (response.data as List)
            .map((json) => CallLogModel.fromJson(json))
            .toList();
        _error = null;
      } else {
        _error = response.message ?? 'Failed to load call logs';
      }
    } catch (e) {
      _error = e.toString();
    } finally {
      _isLoading = false;
      notifyListeners();
    }
  }

  Future<bool> saveCallLog(CallLogModel callLog) async {
    try {
      final response = await _apiService.post(
        ApiConfig.saveCallLog,
        data: callLog.toJson(),
      );

      if (response.success) {
        await loadCallStatistics();
        return true;
      } else {
        _error = response.message ?? 'Failed to save call log';
        return false;
      }
    } catch (e) {
      _error = e.toString();
      return false;
    }
  }

  void clearError() {
    _error = null;
    notifyListeners();
  }
}

