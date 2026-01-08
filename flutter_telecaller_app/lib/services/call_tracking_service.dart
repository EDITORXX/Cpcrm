import 'dart:async';
import 'package:telecaller_crm/models/call_log_model.dart';
import 'package:telecaller_crm/providers/call_tracking_provider.dart';

class CallTrackingService {
  DateTime? _callStartTime;
  int? _currentLeadId;
  int? _currentTaskId;
  String? _currentPhoneNumber;
  StreamSubscription? _phoneStateSubscription;

  Future<void> startCallTracking({
    required int leadId,
    required String phoneNumber,
    int? taskId,
  }) async {
    _currentLeadId = leadId;
    _currentTaskId = taskId;
    _currentPhoneNumber = phoneNumber;
    _callStartTime = DateTime.now();

    // Start listening to phone state changes
    // Note: This requires phone_state or flutter_phone_state package
    // Implementation would depend on the specific package used
  }

  Future<void> endCallTracking() async {
    if (_callStartTime == null) return;

    final endTime = DateTime.now();
    final duration = endTime.difference(_callStartTime!).inSeconds;

    if (_currentLeadId != null && _currentPhoneNumber != null) {
      final callLog = CallLogModel(
        leadId: _currentLeadId!,
        taskId: _currentTaskId,
        phoneNumber: _currentPhoneNumber!,
        callType: 'outgoing',
        startTime: _callStartTime!,
        endTime: endTime,
        duration: duration,
        status: 'completed',
      );

      // Save to local storage and sync to backend
      // This would be handled by CallTrackingProvider
    }

    _reset();
  }

  void _reset() {
    _callStartTime = null;
    _currentLeadId = null;
    _currentTaskId = null;
    _currentPhoneNumber = null;
  }

  void dispose() {
    _phoneStateSubscription?.cancel();
    _reset();
  }
}

