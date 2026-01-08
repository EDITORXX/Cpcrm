import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import 'package:telecaller_crm/providers/task_provider.dart';
import 'package:telecaller_crm/models/task_model.dart';
import 'package:telecaller_crm/utils/helpers.dart';
import 'package:telecaller_crm/config/theme_config.dart';

class TaskListScreen extends StatefulWidget {
  const TaskListScreen({super.key});

  @override
  State<TaskListScreen> createState() => _TaskListScreenState();
}

class _TaskListScreenState extends State<TaskListScreen> {
  @override
  void initState() {
    super.initState();
    WidgetsBinding.instance.addPostFrameCallback((_) {
      Provider.of<TaskProvider>(context, listen: false).loadTasks();
    });
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(
        title: const Text('Tasks'),
      ),
      body: Column(
        children: [
          _FilterBar(),
          Expanded(
            child: Consumer<TaskProvider>(
              builder: (context, taskProvider, _) {
                if (taskProvider.isLoading && taskProvider.tasks.isEmpty) {
                  return const Center(child: CircularProgressIndicator());
                }

                if (taskProvider.error != null) {
                  return Center(
                    child: Text('Error: ${taskProvider.error}'),
                  );
                }

                if (taskProvider.tasks.isEmpty) {
                  return const Center(child: Text('No tasks found'));
                }

                return RefreshIndicator(
                  onRefresh: () => taskProvider.loadTasks(refresh: true),
                  child: ListView.builder(
                    itemCount: taskProvider.tasks.length,
                    itemBuilder: (context, index) {
                      return _TaskCard(task: taskProvider.tasks[index]);
                    },
                  ),
                );
              },
            ),
          ),
        ],
      ),
    );
  }
}

class _FilterBar extends StatelessWidget {
  @override
  Widget build(BuildContext context) {
    return Consumer<TaskProvider>(
      builder: (context, taskProvider, _) {
        return Container(
          padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 8),
          child: SingleChildScrollView(
            scrollDirection: Axis.horizontal,
            child: Row(
              children: [
                _FilterButton(
                  label: 'Pending',
                  isActive: taskProvider.currentFilter == 'pending',
                  onTap: () => taskProvider.filterTasks('pending'),
                ),
                const SizedBox(width: 8),
                _FilterButton(
                  label: 'Completed',
                  isActive: taskProvider.currentFilter == 'completed',
                  onTap: () => taskProvider.filterTasks('completed'),
                ),
                const SizedBox(width: 8),
                _FilterButton(
                  label: 'Rescheduled',
                  isActive: taskProvider.currentFilter == 'rescheduled',
                  onTap: () => taskProvider.filterTasks('rescheduled'),
                ),
                const SizedBox(width: 8),
                _FilterButton(
                  label: 'All',
                  isActive: taskProvider.currentFilter == 'all',
                  onTap: () => taskProvider.filterTasks('all'),
                ),
              ],
            ),
          ),
        );
      },
    );
  }
}

class _FilterButton extends StatelessWidget {
  final String label;
  final bool isActive;
  final VoidCallback onTap;

  const _FilterButton({
    required this.label,
    required this.isActive,
    required this.onTap,
  });

  @override
  Widget build(BuildContext context) {
    return ElevatedButton(
      onPressed: onTap,
      style: ElevatedButton.styleFrom(
        backgroundColor: isActive
            ? ThemeConfig.primaryColor
            : Colors.grey[300],
        foregroundColor: isActive ? Colors.white : Colors.black87,
      ),
      child: Text(label),
    );
  }
}

class _TaskCard extends StatelessWidget {
  final TaskModel task;

  const _TaskCard({required this.task});

  @override
  Widget build(BuildContext context) {
    return Card(
      margin: const EdgeInsets.symmetric(horizontal: 16, vertical: 8),
      child: Padding(
        padding: const EdgeInsets.all(16),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Row(
              mainAxisAlignment: MainAxisAlignment.spaceBetween,
              children: [
                Expanded(
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      Text(
                        task.leadName,
                        style: Theme.of(context).textTheme.titleMedium,
                      ),
                      const SizedBox(height: 4),
                      Text(
                        task.leadPhone,
                        style: Theme.of(context).textTheme.bodyMedium,
                      ),
                    ],
                  ),
                ),
                Container(
                  padding: const EdgeInsets.symmetric(
                    horizontal: 12,
                    vertical: 6,
                  ),
                  decoration: BoxDecoration(
                    color: _getStatusColor(task.status),
                    borderRadius: BorderRadius.circular(12),
                  ),
                  child: Text(
                    task.status.toUpperCase(),
                    style: const TextStyle(
                      color: Colors.white,
                      fontSize: 12,
                      fontWeight: FontWeight.bold,
                    ),
                  ),
                ),
              ],
            ),
            if (task.scheduledAt != null) ...[
              const SizedBox(height: 8),
              Text(
                'Scheduled: ${Helpers.formatDateTime(task.scheduledAt)}',
                style: Theme.of(context).textTheme.bodySmall,
              ),
            ],
            const SizedBox(height: 12),
            SizedBox(
              width: double.infinity,
              child: ElevatedButton.icon(
                onPressed: () => _handleCall(context),
                icon: const Icon(Icons.phone),
                label: const Text('Call'),
              ),
            ),
          ],
        ),
      ),
    );
  }

  Color _getStatusColor(String status) {
    switch (status.toLowerCase()) {
      case 'pending':
        return ThemeConfig.warningColor;
      case 'completed':
        return ThemeConfig.successColor;
      case 'rescheduled':
        return ThemeConfig.primaryColor;
      default:
        return Colors.grey;
    }
  }

  Future<void> _handleCall(BuildContext context) async {
    await Helpers.makePhoneCall(task.leadPhone);
    // Show outcome popup after call
    if (context.mounted) {
      _showOutcomeDialog(context);
    }
  }

  void _showOutcomeDialog(BuildContext context) {
    showDialog(
      context: context,
      builder: (context) => _OutcomeDialog(task: task),
    );
  }
}

class _OutcomeDialog extends StatelessWidget {
  final TaskModel task;

  const _OutcomeDialog({required this.task});

  @override
  Widget build(BuildContext context) {
    return AlertDialog(
      title: const Text('Call Outcome'),
      content: Column(
        mainAxisSize: MainAxisSize.min,
        children: [
          ElevatedButton.icon(
            onPressed: () => _handleOutcome(context, 'interested'),
            icon: const Icon(Icons.thumb_up),
            label: const Text('Interested'),
            style: ElevatedButton.styleFrom(
              backgroundColor: ThemeConfig.successColor,
            ),
          ),
          const SizedBox(height: 8),
          ElevatedButton.icon(
            onPressed: () => _handleOutcome(context, 'not_interested'),
            icon: const Icon(Icons.thumb_down),
            label: const Text('Not Interested'),
            style: ElevatedButton.styleFrom(
              backgroundColor: ThemeConfig.errorColor,
            ),
          ),
          const SizedBox(height: 8),
          ElevatedButton.icon(
            onPressed: () => _handleOutcome(context, 'cnp'),
            icon: const Icon(Icons.phone_missed),
            label: const Text('CNP'),
          ),
          const SizedBox(height: 8),
          ElevatedButton.icon(
            onPressed: () => _handleOutcome(context, 'call_again'),
            icon: const Icon(Icons.schedule),
            label: const Text('Call Again'),
          ),
          const SizedBox(height: 8),
          ElevatedButton.icon(
            onPressed: () => _handleOutcome(context, 'block'),
            icon: const Icon(Icons.block),
            label: const Text('Block'),
            style: ElevatedButton.styleFrom(
              backgroundColor: ThemeConfig.errorColor,
            ),
          ),
        ],
      ),
    );
  }

  void _handleOutcome(BuildContext context, String outcome) {
    Navigator.pop(context);
    // Handle outcome based on type
    if (outcome == 'interested') {
      // Navigate to prospect form
    } else {
      Provider.of<TaskProvider>(context, listen: false)
          .recordOutcome(task.id, outcome);
    }
  }
}

