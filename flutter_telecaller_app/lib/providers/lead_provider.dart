import 'package:flutter/foundation.dart';
import 'package:telecaller_crm/services/api_service.dart';
import 'package:telecaller_crm/config/api_config.dart';
import 'package:telecaller_crm/models/lead_model.dart';
import 'package:telecaller_crm/models/api_response_model.dart';

class LeadProvider with ChangeNotifier {
  final ApiService _apiService = ApiService();
  List<LeadModel> _leads = [];
  bool _isLoading = false;
  String? _error;
  String _searchQuery = '';
  String? _statusFilter;
  int _currentPage = 1;
  int _totalPages = 1;

  List<LeadModel> get leads => _leads;
  bool get isLoading => _isLoading;
  String? get error => _error;
  String get searchQuery => _searchQuery;
  String? get statusFilter => _statusFilter;

  Future<void> loadLeads({bool refresh = false}) async {
    if (refresh) {
      _currentPage = 1;
    }

    _isLoading = true;
    _error = null;
    notifyListeners();

    try {
      final queryParams = <String, dynamic>{
        'page': _currentPage,
        'per_page': 50,
      };

      if (_searchQuery.isNotEmpty) {
        queryParams['search'] = _searchQuery;
      }
      if (_statusFilter != null && _statusFilter!.isNotEmpty) {
        queryParams['status'] = _statusFilter;
      }

      final response = await _apiService.get<Map<String, dynamic>>(
        ApiConfig.leads,
        queryParameters: queryParams,
        fromJson: (data) => data as Map<String, dynamic>,
      );

      if (response.success && response.data != null) {
        final data = response.data!['data'] as List?;
        final pagination = response.data!['pagination'] as Map<String, dynamic>?;

        if (data != null) {
          if (refresh) {
            _leads = data.map((json) => LeadModel.fromJson(json)).toList();
          } else {
            _leads.addAll(data.map((json) => LeadModel.fromJson(json)).toList());
          }

          if (pagination != null) {
            _currentPage = pagination['current_page'] ?? 1;
            _totalPages = pagination['last_page'] ?? 1;
          }
        }
        _error = null;
      } else {
        _error = response.message ?? 'Failed to load leads';
      }
    } catch (e) {
      _error = e.toString();
    } finally {
      _isLoading = false;
      notifyListeners();
    }
  }

  void setSearchQuery(String query) {
    _searchQuery = query;
    loadLeads(refresh: true);
  }

  void setStatusFilter(String? status) {
    _statusFilter = status;
    loadLeads(refresh: true);
  }

  void clearError() {
    _error = null;
    notifyListeners();
  }
}

