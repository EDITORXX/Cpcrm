class CallLogModel {
  final int? id;
  final int leadId;
  final int? taskId;
  final String phoneNumber;
  final String callType; // 'incoming' or 'outgoing'
  final DateTime startTime;
  final DateTime? endTime;
  final int duration; // in seconds
  final String? status; // 'completed', 'missed', 'rejected'
  final bool synced;

  CallLogModel({
    this.id,
    required this.leadId,
    this.taskId,
    required this.phoneNumber,
    required this.callType,
    required this.startTime,
    this.endTime,
    required this.duration,
    this.status,
    this.synced = false,
  });

  factory CallLogModel.fromJson(Map<String, dynamic> json) {
    return CallLogModel(
      id: json['id'],
      leadId: json['lead_id'] ?? 0,
      taskId: json['task_id'],
      phoneNumber: json['phone_number'] ?? '',
      callType: json['call_type'] ?? 'outgoing',
      startTime: DateTime.parse(json['start_time']),
      endTime: json['end_time'] != null ? DateTime.parse(json['end_time']) : null,
      duration: json['duration'] ?? 0,
      status: json['status'] ?? 'completed',
      synced: json['synced'] ?? false,
    );
  }

  Map<String, dynamic> toJson() {
    return {
      'id': id,
      'lead_id': leadId,
      'task_id': taskId,
      'phone_number': phoneNumber,
      'call_type': callType,
      'start_time': startTime.toIso8601String(),
      'end_time': endTime?.toIso8601String(),
      'duration': duration,
      'status': status ?? 'completed',
      'synced': synced,
    };
  }

  String get formattedDuration {
    final hours = duration ~/ 3600;
    final minutes = (duration % 3600) ~/ 60;
    final seconds = duration % 60;
    
    if (hours > 0) {
      return '${hours}h ${minutes}m ${seconds}s';
    } else if (minutes > 0) {
      return '${minutes}m ${seconds}s';
    } else {
      return '${seconds}s';
    }
  }
}

