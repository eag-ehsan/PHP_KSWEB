<?php

    function getServerInfo() {
        $data = array();
        $data['phpVersion'] = phpversion();
        $data['serverSoftware'] = isset($_SERVER['SERVER_SOFTWARE']) ? $_SERVER['SERVER_SOFTWARE'] : 'Unknown';
        $data['localhostIp'] = getLocalhostIp();
        $data['wifiIp'] = getWifiIpRobust();
        return $data;
    }

    function getLocalhostIp() {
        $ip = isset($_SERVER['SERVER_ADDR']) ? $_SERVER['SERVER_ADDR'] : '';

        if ($ip === '::1' || filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6)) {
            $hostname = gethostname();
            if ($hostname) {
                $ipv4 = gethostbyname($hostname);
                if (filter_var($ipv4, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4)) {
                    $ip = $ipv4;
                }
            }
        }
        return $ip;
    }

    function getWifiIpRobust() {
        $ip = "";
        $sock = @socket_create(AF_INET, SOCK_DGRAM, SOL_UDP);
        if ($sock) {
            if (@socket_connect($sock, "8.8.8.8", 53)) {
                @socket_getsockname($sock, $ip, $port);
            }
            @socket_close($sock);
        }
        if ($ip && filter_var($ip, FILTER_VALIDATE_IP) && $ip !== "127.0.0.1") {
            return $ip;
        }
        return "";
    }

    if (isset($_GET['viewInfo'])) {
        phpinfo();
        exit;
    }

    $info = getServerInfo();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>KSWEB — Welcome</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style>
        :root {
            --bg-color: #f7f9fc;
            --card-bg: #ffffff;
            --primary-color: #007bff; /* Чистый синий */
            --text-color: #333;
            --accent-bg: #e9ecef;
            --border-color: #dee2e6;
        }
        body {
            background: var(--bg-color);
            font-family: 'Segoe UI', Roboto, Helvetica, Arial, sans-serif;
            margin: 0;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            color: var(--text-color);
        }
        .container {
            background: var(--card-bg);
            padding: 30px;
            border-radius: 12px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.05);
            max-width: 600px;
            width: 90%;
            animation: fadeIn .6s ease;
        }
        h1 {
            margin-top: 0;
            font-size: 28px;
            color: var(--primary-color);
            margin-bottom: 10px;
            display: flex;
            align-items: center;
        }
        h1 span { margin-left: 10px; font-size: 1.5rem; }

        p {
            font-size: 15px;
            line-height: 1.6;
        }
        a.btn {
            display: inline-block;
            margin-top: 20px;
            padding: 12px 24px;
            background: var(--primary-color);
            color: #fff;
            text-decoration: none;
            border-radius: 6px;
            transition: background .25s, transform 0.1s;
            font-weight: 500;
        }
        a.btn:hover {
            background: #0056b3;
            transform: translateY(-1px);
        }
        .info-block {
            background: var(--accent-bg);
            padding: 15px;
            border-radius: 8px;
            margin: 25px 0;
            font-size: 14px;
        }
        .info-title {
            font-weight: bold;
            margin-bottom: 10px;
            color: #495057;
            border-bottom: 1px solid var(--border-color);
            padding-bottom: 8px;
        }
        .info-row {
            display: flex;
            justify-content: space-between;
            padding: 6px 0;
        }
        .info-row > div:first-child {
            color: #6c757d;
        }
        
        .info-row > div:last-child {
            word-break: break-all;
            text-align: right;
            max-width: 65%;
            font-weight: 500;
        }
        
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(12px); }
            to { opacity: 1; transform: translateY(0); }
        }
    </style>
</head>
<body>
<div class="container">

    <h1>Welcome to KSWEB!<span>🎉</span></h1>
    <p>Congratulations! You are seeing this page because your KSWEB-based server is up and running successfully.</p>

    <div class="info-block">
        <div class="info-title">💻 Server Information</div>

        <div class="info-row"><div>PHP version</div><div><?= htmlspecialchars($info['phpVersion']) ?></div></div>
        <div class="info-row"><div>Server software</div><div><?= htmlspecialchars($info['serverSoftware']) ?></div></div>
		
		<div class="info-row"><div>This script path</div><div><?= htmlspecialchars(__FILE__) ?></div></div>

        <?php if ($info['localhostIp']): ?>
        <div class="info-row"><div>Local IP Address</div><div><?= htmlspecialchars($info['localhostIp']) ?></div></div>
        <?php endif; ?>

        <?php if ($info['wifiIp']): ?>
        <div class="info-row"><div>🌐 Wi-Fi IP Address</div><div><?= htmlspecialchars($info['wifiIp']) ?></div></div>
        <?php endif; ?>

    </div>

	<p>Default MySQL login data: login "<b>root</b>" with empty password! Use it to enter to phpMyAdmin or adminer.</p>
	<p>Use KSWEB menu item "Tools" to configure the entrance to KSWEB Web Interface. Default login data:</a> <b>login</b> and <b>password</b> are both "admin" by default.</p>
	<p>We strongly recommend to change all passwords for security reasons!</p>

    <a href="index.php?viewInfo=1" class="btn">Show PHP info</a>

</div>
</body>
</html>