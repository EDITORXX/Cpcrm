class TaskModel {
  final int id;
  final int leadId;
  final String leadName;
  final String leadPhone;
  final String? leadEmail;
  final String? managerName;
  final int? managerId;
  final String taskType;
  final String status;
  final String? scheduledAt;
  final String? completedAt;
  final String? outcome;
  final String? notes;

  TaskModel({
    required this.id,
    required this.leadId,
    required this.leadName,
    required this.leadPhone,
    this.leadEmail,
    this.managerName,
    this.managerId,
    required this.taskType,
    required this.status,
    this.scheduledAt,
    this.completedAt,
    this.outcome,
    this.notes,
  });

  factory TaskModel.fromJson(Map<String, dynamic> json) {
    return TaskModel(
      id: json['id'] ?? 0,
      leadId: json['lead_id'] ?? 0,
      leadName: json['lead_name'] ?? '-',
      leadPhone: json['lead_phone'] ?? '-',
      leadEmail: json['lead_email'],
      managerName: json['manager_name'] ?? 'Not Assigned',
      managerId: json['manager_id'],
      taskType: json['task_type'] ?? 'calling',
      status: json['status'] ?? 'pending',
      scheduledAt: json['scheduled_at'],
      completedAt: json['completed_at'],
      outcome: json['outcome'],
      notes: json['notes'],
    );
  }

  Map<String, dynamic> toJson() {
    return {
      'id': id,
      'lead_id': leadId,
      'lead_name': leadName,
      'lead_phone': leadPhone,
      'lead_email': leadEmail,
      'manager_name': managerName,
      'manager_id': managerId,
      'task_type': taskType,
      'status': status,
      'scheduled_at': scheduledAt,
      'completed_at': completedAt,
      'outcome': outcome,
      'notes': notes,
    };
  }
}

