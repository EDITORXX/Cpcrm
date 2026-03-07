import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import 'package:firebase_core/firebase_core.dart';
import 'package:telecaller_crm/config/theme_config.dart';
import 'package:telecaller_crm/providers/auth_provider.dart';
import 'package:telecaller_crm/providers/lead_provider.dart';
import 'package:telecaller_crm/providers/task_provider.dart';
import 'package:telecaller_crm/providers/prospect_provider.dart';
import 'package:telecaller_crm/providers/call_tracking_provider.dart';
import 'package:telecaller_crm/screens/auth/splash_screen.dart';
import 'package:telecaller_crm/services/fcm_service.dart';

void main() async {
  WidgetsFlutterBinding.ensureInitialized();
  await Firebase.initializeApp();
  runApp(const MyApp());
}

class MyApp extends StatelessWidget {
  const MyApp({super.key});

  @override
  Widget build(BuildContext context) {
    return MultiProvider(
      providers: [
        ChangeNotifierProvider(create: (_) => AuthProvider()),
        ChangeNotifierProvider(create: (_) => LeadProvider()),
        ChangeNotifierProvider(create: (_) => TaskProvider()),
        ChangeNotifierProvider(create: (_) => ProspectProvider()),
        ChangeNotifierProvider(create: (_) => CallTrackingProvider()),
      ],
      child: MaterialApp(
        title: 'Base CRM',
        debugShowCheckedModeBanner: false,
        theme: ThemeConfig.lightTheme,
        home: const SplashScreen(),
      ),
    );
  }
}
