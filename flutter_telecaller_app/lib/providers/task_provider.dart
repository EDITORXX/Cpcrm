import 'package:flutter/foundation.dart';
import 'package:telecaller_crm/services/api_service.dart';
import 'package:telecaller_crm/config/api_config.dart';
import 'package:telecaller_crm/models/task_model.dart';
import 'package:telecaller_crm/models/api_response_model.dart';

class TaskProvider with ChangeNotifier {
  final ApiService _apiService = ApiService();
  List<TaskModel> _tasks = [];
  bool _isLoading = false;
  String? _error;
  String _currentFilter = 'pending';
  int _currentPage = 1;
  int _totalPages = 1;

  List<TaskModel> get tasks => _tasks;
  bool get isLoading => _isLoading;
  String? get error => _error;
  String get currentFilter => _currentFilter;
  int get currentPage => _currentPage;
  int get totalPages => _totalPages;

  Future<void> loadTasks({String? status, bool refresh = false}) async {
    if (refresh) {
      _currentPage = 1;
    }

    _isLoading = true;
    _error = null;
    if (status != null) {
      _currentFilter = status;
    }
    notifyListeners();

    try {
      final response = await _apiService.get<Map<String, dynamic>>(
        ApiConfig.tasks,
        queryParameters: {
          'status': _currentFilter == 'all' ? null : _currentFilter,
          'page': _currentPage,
          'per_page': 50,
        },
        fromJson: (data) => data as Map<String, dynamic>,
      );

      if (response.success && response.data != null) {
        final data = response.data!['data'] as List?;
        final pagination = response.data!['pagination'] as Map<String, dynamic>?;

        if (data != null) {
          if (refresh) {
            _tasks = data.map((json) => TaskModel.fromJson(json)).toList();
          } else {
            _tasks.addAll(data.map((json) => TaskModel.fromJson(json)).toList());
          }

          if (pagination != null) {
            _currentPage = pagination['current_page'] ?? 1;
            _totalPages = pagination['last_page'] ?? 1;
          }
        }
        _error = null;
      } else {
        _error = response.message ?? 'Failed to load tasks';
      }
    } catch (e) {
      _error = e.toString();
    } finally {
      _isLoading = false;
      notifyListeners();
    }
  }

  Future<bool> recordOutcome(
    int taskId,
    String outcome, {
    String? scheduledAt,
    String? notes,
  }) async {
    _isLoading = true;
    notifyListeners();

    try {
      final data = {
        'outcome': outcome,
        if (scheduledAt != null) 'scheduled_at': scheduledAt,
        if (notes != null) 'notes': notes,
      };

      final response = await _apiService.post(
        ApiConfig.recordOutcome(taskId),
        data: data,
      );

      if (response.success) {
        await loadTasks(refresh: true);
        return true;
      } else {
        _error = response.message ?? 'Failed to record outcome';
        return false;
      }
    } catch (e) {
      _error = e.toString();
      return false;
    } finally {
      _isLoading = false;
      notifyListeners();
    }
  }

  void filterTasks(String status) {
    _currentFilter = status;
    loadTasks(refresh: true);
  }

  void clearError() {
    _error = null;
    notifyListeners();
  }
}

