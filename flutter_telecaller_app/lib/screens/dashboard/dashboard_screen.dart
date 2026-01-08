import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import 'package:telecaller_crm/providers/auth_provider.dart';
import 'package:telecaller_crm/providers/task_provider.dart';
import 'package:telecaller_crm/providers/call_tracking_provider.dart';
import 'package:telecaller_crm/screens/tasks/task_list_screen.dart';
import 'package:telecaller_crm/screens/leads/lead_list_screen.dart';
import 'package:telecaller_crm/screens/prospects/prospect_list_screen.dart';
import 'package:telecaller_crm/screens/profile/profile_screen.dart';
import 'package:telecaller_crm/screens/calls/call_statistics_screen.dart';
import 'package:telecaller_crm/config/theme_config.dart';

class DashboardScreen extends StatefulWidget {
  const DashboardScreen({super.key});

  @override
  State<DashboardScreen> createState() => _DashboardScreenState();
}

class _DashboardScreenState extends State<DashboardScreen> {
  int _currentIndex = 0;

  final List<Widget> _screens = [
    const DashboardHome(),
    const TaskListScreen(),
    const LeadListScreen(),
    const ProspectListScreen(),
    const ProfileScreen(),
  ];

  @override
  void initState() {
    super.initState();
    WidgetsBinding.instance.addPostFrameCallback((_) {
      Provider.of<TaskProvider>(context, listen: false).loadTasks();
      Provider.of<CallTrackingProvider>(context, listen: false)
          .loadCallStatistics();
    });
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      body: _screens[_currentIndex],
      bottomNavigationBar: BottomNavigationBar(
        currentIndex: _currentIndex,
        onTap: (index) {
          setState(() {
            _currentIndex = index;
          });
        },
        type: BottomNavigationBarType.fixed,
        selectedItemColor: ThemeConfig.primaryColor,
        unselectedItemColor: Colors.grey,
        items: const [
          BottomNavigationBarItem(
            icon: Icon(Icons.dashboard),
            label: 'Dashboard',
          ),
          BottomNavigationBarItem(
            icon: Icon(Icons.task),
            label: 'Tasks',
          ),
          BottomNavigationBarItem(
            icon: Icon(Icons.people),
            label: 'Leads',
          ),
          BottomNavigationBarItem(
            icon: Icon(Icons.verified_user),
            label: 'Verification',
          ),
          BottomNavigationBarItem(
            icon: Icon(Icons.person),
            label: 'Profile',
          ),
        ],
      ),
    );
  }
}

class DashboardHome extends StatelessWidget {
  const DashboardHome({super.key});

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(
        title: const Text('Dashboard'),
        actions: [
          IconButton(
            icon: const Icon(Icons.bar_chart),
            onPressed: () {
              Navigator.push(
                context,
                MaterialPageRoute(
                  builder: (_) => const CallStatisticsScreen(),
                ),
              );
            },
          ),
        ],
      ),
      body: RefreshIndicator(
        onRefresh: () async {
          await Future.wait([
            Provider.of<TaskProvider>(context, listen: false).loadTasks(),
            Provider.of<CallTrackingProvider>(context, listen: false)
                .loadCallStatistics(),
          ]);
        },
        child: SingleChildScrollView(
          padding: const EdgeInsets.all(16),
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.stretch,
            children: [
              const StatsCards(),
              const SizedBox(height: 24),
              const CallStatsCards(),
              const SizedBox(height: 24),
              const QuickActions(),
            ],
          ),
        ),
      ),
    );
  }
}

class StatsCards extends StatelessWidget {
  const StatsCards({super.key});

  @override
  Widget build(BuildContext context) {
    return Consumer<TaskProvider>(
      builder: (context, taskProvider, _) {
        final pendingTasks = taskProvider.tasks
            .where((t) => t.status == 'pending')
            .length;
        final completedTasks = taskProvider.tasks
            .where((t) => t.status == 'completed')
            .length;

        return Column(
          children: [
            Row(
              children: [
                Expanded(
                  child: _StatCard(
                    title: 'Pending Tasks',
                    value: pendingTasks.toString(),
                    icon: Icons.pending,
                    color: ThemeConfig.warningColor,
                  ),
                ),
                const SizedBox(width: 16),
                Expanded(
                  child: _StatCard(
                    title: 'Completed',
                    value: completedTasks.toString(),
                    icon: Icons.check_circle,
                    color: ThemeConfig.successColor,
                  ),
                ),
              ],
            ),
          ],
        );
      },
    );
  }
}

class CallStatsCards extends StatelessWidget {
  const CallStatsCards({super.key});

  @override
  Widget build(BuildContext context) {
    return Consumer<CallTrackingProvider>(
      builder: (context, callProvider, _) {
        return Column(
          crossAxisAlignment: CrossAxisAlignment.stretch,
          children: [
            Text(
              'Call Statistics',
              style: Theme.of(context).textTheme.headlineMedium,
            ),
            const SizedBox(height: 16),
            Row(
              children: [
                Expanded(
                  child: _StatCard(
                    title: "Today's Calls",
                    value: callProvider.todayTotalCalls.toString(),
                    icon: Icons.phone,
                    color: ThemeConfig.primaryColor,
                  ),
                ),
                const SizedBox(width: 16),
                Expanded(
                  child: _StatCard(
                    title: 'Talking Time',
                    value: callProvider.formattedTalkingTime,
                    icon: Icons.timer,
                    color: ThemeConfig.secondaryColor,
                  ),
                ),
              ],
            ),
            const SizedBox(height: 16),
            Row(
              children: [
                Expanded(
                  child: _StatCard(
                    title: 'Incoming',
                    value: callProvider.todayIncomingCalls.toString(),
                    icon: Icons.phone_callback,
                    color: ThemeConfig.successColor,
                  ),
                ),
                const SizedBox(width: 16),
                Expanded(
                  child: _StatCard(
                    title: 'Outgoing',
                    value: callProvider.todayOutgoingCalls.toString(),
                    icon: Icons.phone_forwarded,
                    color: ThemeConfig.primaryColor,
                  ),
                ),
              ],
            ),
          ],
        );
      },
    );
  }
}

class _StatCard extends StatelessWidget {
  final String title;
  final String value;
  final IconData icon;
  final Color color;

  const _StatCard({
    required this.title,
    required this.value,
    required this.icon,
    required this.color,
  });

  @override
  Widget build(BuildContext context) {
    return Card(
      child: Padding(
        padding: const EdgeInsets.all(16),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Row(
              mainAxisAlignment: MainAxisAlignment.spaceBetween,
              children: [
                Icon(icon, color: color, size: 32),
                Text(
                  value,
                  style: Theme.of(context).textTheme.headlineMedium?.copyWith(
                        color: color,
                        fontWeight: FontWeight.bold,
                      ),
                ),
              ],
            ),
            const SizedBox(height: 8),
            Text(
              title,
              style: Theme.of(context).textTheme.bodyMedium?.copyWith(
                    color: Colors.grey[600],
                  ),
            ),
          ],
        ),
      ),
    );
  }
}

class QuickActions extends StatelessWidget {
  const QuickActions({super.key});

  @override
  Widget build(BuildContext context) {
    return Column(
      crossAxisAlignment: CrossAxisAlignment.stretch,
      children: [
        Text(
          'Quick Actions',
          style: Theme.of(context).textTheme.headlineMedium,
        ),
        const SizedBox(height: 16),
        GridView.count(
          shrinkWrap: true,
          physics: const NeverScrollableScrollPhysics(),
          crossAxisCount: 2,
          crossAxisSpacing: 16,
          mainAxisSpacing: 16,
          children: [
            _QuickActionCard(
              title: 'View Tasks',
              icon: Icons.task,
              onTap: () {
                // Navigate to tasks
              },
            ),
            _QuickActionCard(
              title: 'View Leads',
              icon: Icons.people,
              onTap: () {
                // Navigate to leads
              },
            ),
            _QuickActionCard(
              title: 'Call Stats',
              icon: Icons.bar_chart,
              onTap: () {
                Navigator.push(
                  context,
                  MaterialPageRoute(
                    builder: (_) => const CallStatisticsScreen(),
                  ),
                );
              },
            ),
            _QuickActionCard(
              title: 'Profile',
              icon: Icons.person,
              onTap: () {
                // Navigate to profile
              },
            ),
          ],
        ),
      ],
    );
  }
}

class _QuickActionCard extends StatelessWidget {
  final String title;
  final IconData icon;
  final VoidCallback onTap;

  const _QuickActionCard({
    required this.title,
    required this.icon,
    required this.onTap,
  });

  @override
  Widget build(BuildContext context) {
    return Card(
      child: InkWell(
        onTap: onTap,
        child: Padding(
          padding: const EdgeInsets.all(16),
          child: Column(
            mainAxisAlignment: MainAxisAlignment.center,
            children: [
              Icon(icon, size: 40, color: ThemeConfig.primaryColor),
              const SizedBox(height: 8),
              Text(
                title,
                textAlign: TextAlign.center,
                style: Theme.of(context).textTheme.bodyMedium,
              ),
            ],
          ),
        ),
      ),
    );
  }
}

