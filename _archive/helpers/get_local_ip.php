<?php
/**
 * Get Local Network IP Address
 * Detects the primary local network IP (not loopback)
 */

function getLocalIP() {
    // Try PowerShell method first (Windows)
    if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
        $command = 'powershell -Command "Get-NetIPAddress -AddressFamily IPv4 | Where-Object {$_.IPAddress -notlike \'127.*\' -and $_.IPAddress -notlike \'169.254.*\'} | Select-Object -First 1 -ExpandProperty IPAddress"';
        $ip = trim(shell_exec($command));
        if (!empty($ip) && filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE) === false) {
            return $ip;
        }
    }
    
    // Fallback: Try ipconfig (Windows)
    if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
        $output = shell_exec('ipconfig');
        preg_match_all('/IPv4 Address[\.\s]+:\s*([0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3})/', $output, $matches);
        if (!empty($matches[1])) {
            foreach ($matches[1] as $ip) {
                // Skip loopback and link-local
                if ($ip !== '127.0.0.1' && !preg_match('/^169\.254\./', $ip)) {
                    return $ip;
                }
            }
        }
    }
    
    // Linux/Mac fallback
    $commands = [
        "hostname -I | awk '{print $1}'",
        "ip route get 8.8.8.8 | awk '{print $7}'",
        "ifconfig | grep -Eo 'inet (addr:)?([0-9]*\.){3}[0-9]*' | grep -Eo '([0-9]*\.){3}[0-9]*' | grep -v '127.0.0.1' | head -1"
    ];
    
    foreach ($commands as $cmd) {
        $ip = trim(shell_exec($cmd));
        if (!empty($ip) && filter_var($ip, FILTER_VALIDATE_IP)) {
            return $ip;
        }
    }
    
    return '127.0.0.1'; // Fallback to localhost
}

// Output IP for batch script usage
echo getLocalIP();
