<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>{{ env('APP_NAME') }} | Admin Dashboard</title>

    <link rel="dns-prefetch" href="//fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=Nunito" rel="stylesheet">
    <link rel="shortcut icon" href="{{ asset('assets/img/favicon.png') }}" type="image/x-icon">

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('assets/css/admin.css') }}">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script type="module" src="https://unpkg.com/ionicons@6.0.3/dist/ionicons/ionicons.esm.js"></script>
    <script nomodule src="https://unpkg.com/ionicons@6.0.3/dist/ionicons/ionicons.js"></script>
</head>
<body>

    <section class="admin-parent">
        {{-- sidebar section --}}
        <aside class="sidebar-section">
            <div class="logo-divide">
                <img src="{{ asset('assets/img/light.png') }}" alt="">
            </div>
            <div class="main-menu">
                <h6 class="sidebar-header">main menu</h6>
                <a href="#" class="icon-link active">
                    <ion-icon name="calendar-outline" class="icon-ctrl"></ion-icon>
                    <h6>overview</h6>
                </a>
                <a href="#" class="icon-link">
                    <ion-icon name="partly-sunny-outline" class="icon-ctrl"></ion-icon>
                    <h6>equipment</h6>
                </a>
                <a href="#" class="icon-link">
                    <ion-icon name="hammer-outline" class="icon-ctrl"></ion-icon>
                    <h6>maintenance</h6>
                </a>
                <a href="#" class="icon-link">
                    <ion-icon name="people-outline" class="icon-ctrl"></ion-icon>
                    <h6>users</h6>
                </a>
                <a href="#" class="icon-link">
                    <ion-icon name="bar-chart-outline" class="icon-ctrl"></ion-icon>
                    <h6>reports</h6>
                </a>
                <a href="#" class="icon-link">
                    <ion-icon name="settings-outline" class="icon-ctrl"></ion-icon>
                    <h6>settings</h6>
                </a>
            </div>
        </aside>

        <div class="admin-body">
            <div class="sidebar-placeholder"></div>
            <div class="admin-content"></div>
        </div>
    </section>
    
</body>
</html>