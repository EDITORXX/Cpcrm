import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import 'package:telecaller_crm/providers/auth_provider.dart';
import 'package:telecaller_crm/providers/task_provider.dart';
import 'package:telecaller_crm/providers/lead_provider.dart';
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
  late List<Widget> _screens;

  @override
  void initState() {
    super.initState();
    WidgetsBinding.instance.addPostFrameCallback((_) {
      final authProvider = Provider.of<AuthProvider>(context, listen: false);
      _screens = _getScreens(authProvider);
      
      Provider.of<LeadProvider>(context, listen: false).loadLeads(refresh: true);
      
      if (authProvider.isTelecaller || authProvider.isSalesExecutive) {
        Provider.of<TaskProvider>(context, listen: false).loadTasks();
        Provider.of<CallTrackingProvider>(context, listen: false)
            .loadCallStatistics();
      }
    });
  }
  
  List<Widget> _getScreens(AuthProvider authProvider) {
    final screens = <Widget>[
      const DashboardHome(),
    ];
    
    if (authProvider.isTelecaller || authProvider.isSalesExecutive) {
      screens.addAll([
        const TaskListScreen(),
        const LeadListScreen(),
        const ProspectListScreen(),
      ]);
    } else if (authProvider.isSalesManager || authProvider.isCrm || authProvider.isAdmin) {
      screens.addAll([
        const LeadListScreen(),
        // Placeholder for reports screen
        const DashboardHome(), // Temporary - can be replaced with ReportsScreen
      ]);
    }
    
    // Profile for all users
    screens.add(const ProfileScreen());
    
    return screens;
  }

  @override
  Widget build(BuildContext context) {
    return Consumer<AuthProvider>(
      builder: (context, authProvider, _) {
        // Update screens based on current role
        _screens = _getScreens(authProvider);
        
        // Role-based navigation items
        final navigationItems = _getNavigationItems(authProvider);
        
        // Ensure current index is valid
        if (_currentIndex >= _screens.length) {
          _currentIndex = 0;
        }
        
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
            items: navigationItems,
          ),
        );
      },
    );
  }
  
  List<BottomNavigationBarItem> _getNavigationItems(AuthProvider authProvider) {
    // Base items for all users
    final items = <BottomNavigationBarItem>[
      const BottomNavigationBarItem(
        icon: Icon(Icons.dashboard),
        label: 'Dashboard',
      ),
    ];
    
    // Role-based items
    if (authProvider.isTelecaller || authProvider.isSalesExecutive) {
      items.addAll([
        const BottomNavigationBarItem(
          icon: Icon(Icons.task),
          label: 'Tasks',
        ),
        const BottomNavigationBarItem(
          icon: Icon(Icons.people),
          label: 'Leads',
        ),
        const BottomNavigationBarItem(
          icon: Icon(Icons.verified_user),
          label: 'Verification',
        ),
      ]);
    } else if (authProvider.isSalesManager || authProvider.isCrm || authProvider.isAdmin) {
      items.addAll([
        const BottomNavigationBarItem(
          icon: Icon(Icons.people),
          label: 'Leads',
        ),
        const BottomNavigationBarItem(
          icon: Icon(Icons.analytics),
          label: 'Reports',
        ),
      ]);
    }
    
    // Profile for all users
    items.add(
      const BottomNavigationBarItem(
        icon: Icon(Icons.person),
        label: 'Profile',
      ),
    );
    
    return items;
  }
}

class DashboardHome extends StatelessWidget {
  const DashboardHome({super.key});

  @override
  Widget build(BuildContext context) {
    return Consumer<AuthProvider>(
      builder: (context, authProvider, _) {
        return Scaffold(
          appBar: AppBar(
            title: Text('Dashboard - ${authProvider.userRoleName}'),
            actions: [
              if (authProvider.isTelecaller || authProvider.isSalesExecutive)
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
              final futures = <Future>[
                Provider.of<LeadProvider>(context, listen: false).loadLeads(refresh: true),
              ];
              if (authProvider.isTelecaller || authProvider.isSalesExecutive) {
                futures.addAll([
                  Provider.of<TaskProvider>(context, listen: false).loadTasks(),
                  Provider.of<CallTrackingProvider>(context, listen: false)
                      .loadCallStatistics(),
                ]);
              }
              await Future.wait(futures);
            },
            child: SingleChildScrollView(
              padding: const EdgeInsets.all(16),
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.stretch,
                children: [
                  // Role-based welcome message
                  _RoleWelcomeCard(userRole: authProvider.userRoleName),
                  const SizedBox(height: 24),
                  // Role-based stats
                  if (authProvider.isTelecaller || authProvider.isSalesExecutive) ...[
                    const StatsCards(),
                    const SizedBox(height: 24),
                    const CallStatsCards(),
                    const SizedBox(height: 24),
                  ] else if (authProvider.isSalesManager || authProvider.isCrm || authProvider.isAdmin) ...[
                    const ManagerStatsCards(),
                    const SizedBox(height: 24),
                  ],
                  const QuickActions(),
                ],
              ),
            ),
          ),
        );
      },
    );
  }
}

class _RoleWelcomeCard extends StatelessWidget {
  final String userRole;
  
  const _RoleWelcomeCard({required this.userRole});

  @override
  Widget build(BuildContext context) {
    return Card(
      color: ThemeConfig.primaryColor.withOpacity(0.1),
      child: Padding(
        padding: const EdgeInsets.all(16),
        child: Row(
          children: [
            Icon(
              Icons.person,
              size: 40,
              color: ThemeConfig.primaryColor,
            ),
            const SizedBox(width: 16),
            Expanded(
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Text(
                    'Welcome!',
                    style: Theme.of(context).textTheme.titleLarge?.copyWith(
                          fontWeight: FontWeight.bold,
                        ),
                  ),
                  Text(
                    'Logged in as $userRole',
                    style: Theme.of(context).textTheme.bodyMedium?.copyWith(
                          color: Colors.grey[600],
                        ),
                  ),
                ],
              ),
            ),
          ],
        ),
      ),
    );
  }
}

class ManagerStatsCards extends StatelessWidget {
  const ManagerStatsCards({super.key});

  @override
  Widget build(BuildContext context) {
    return Consumer<LeadProvider>(
      builder: (context, leadProvider, _) {
        final totalLeads = leadProvider.leads.length;
        final newLeads = leadProvider.leads.where((l) => l.status == 'new').length;
        final contactedLeads = leadProvider.leads.where((l) => l.status == 'contacted').length;
        
        return Column(
          crossAxisAlignment: CrossAxisAlignment.stretch,
          children: [
            Text(
              'Overview',
              style: Theme.of(context).textTheme.headlineMedium,
            ),
            const SizedBox(height: 16),
            Row(
              children: [
                Expanded(
                  child: _StatCard(
                    title: 'Total Leads',
                    value: totalLeads.toString(),
                    icon: Icons.people,
                    color: ThemeConfig.primaryColor,
                  ),
                ),
                const SizedBox(width: 16),
                Expanded(
                  child: _StatCard(
                    title: 'New Leads',
                    value: newLeads.toString(),
                    icon: Icons.fiber_new,
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
                    title: 'Contacted',
                    value: contactedLeads.toString(),
                    icon: Icons.phone_callback,
                    color: ThemeConfig.warningColor,
                  ),
                ),
                const SizedBox(width: 16),
                Expanded(
                  child: _StatCard(
                    title: 'Conversion',
                    value: totalLeads > 0
                        ? '${((contactedLeads / totalLeads) * 100).toStringAsFixed(0)}%'
                        : '0%',
                    icon: Icons.trending_up,
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

class StatsCards extends StatelessWidget {
  const StatsCards({super.key});

  @override
  Widget build(BuildContext context) {
    return Consumer2<TaskProvider, LeadProvider>(
      builder: (context, taskProvider, leadProvider, _) {
        final pendingTasks = taskProvider.tasks
            .where((t) => t.status == 'pending')
            .length;
        final completedTasks = taskProvider.tasks
            .where((t) => t.status == 'completed')
            .length;
        final totalLeads = leadProvider.leads.length;

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
            const SizedBox(height: 16),
            Row(
              children: [
                Expanded(
                  child: _StatCard(
                    title: 'Total Leads',
                    value: totalLeads.toString(),
                    icon: Icons.people,
                    color: ThemeConfig.primaryColor,
                  ),
                ),
                const SizedBox(width: 16),
                const Expanded(child: SizedBox()),
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
    return Consumer<AuthProvider>(
      builder: (context, authProvider, _) {
        final actions = _getQuickActions(context, authProvider);
        
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
              children: actions,
            ),
          ],
        );
      },
    );
  }
  
  List<Widget> _getQuickActions(BuildContext context, AuthProvider authProvider) {
    final actions = <Widget>[];
    
    if (authProvider.isTelecaller || authProvider.isSalesExecutive) {
      actions.addAll([
        _QuickActionCard(
          title: 'View Tasks',
          icon: Icons.task,
          onTap: () {
            // Navigate to tasks - handled by bottom nav
          },
        ),
        _QuickActionCard(
          title: 'View Leads',
          icon: Icons.people,
          onTap: () {
            // Navigate to leads - handled by bottom nav
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
      ]);
    } else if (authProvider.isSalesManager || authProvider.isCrm || authProvider.isAdmin) {
      actions.addAll([
        _QuickActionCard(
          title: 'View Leads',
          icon: Icons.people,
          onTap: () {
            // Navigate to leads - handled by bottom nav
          },
        ),
        _QuickActionCard(
          title: 'Team Stats',
          icon: Icons.analytics,
          onTap: () {
            // Navigate to team stats
          },
        ),
        if (authProvider.isAdmin || authProvider.isCrm)
          _QuickActionCard(
            title: 'System Settings',
            icon: Icons.settings,
            onTap: () {
              // Navigate to settings
            },
          ),
      ]);
    }
    
    // Profile for all users
    actions.add(
      _QuickActionCard(
        title: 'Profile',
        icon: Icons.person,
        onTap: () {
          // Navigate to profile - handled by bottom nav
        },
      ),
    );
    
    return actions;
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

