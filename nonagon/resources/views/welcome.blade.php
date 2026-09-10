<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Nonagon | Landing Page</title>
    <link rel="shortcut icon" href="{{ asset('assets/img/favicon.png') }}" type="image/x-icon">
    
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:ital,opsz,wght@0,9..40,100..1000;1,9..40,100..1000&display=swap" rel="stylesheet">

    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css"
        integrity="sha384-xOolHFLEh07PJGoPkLv1IbcEPTNtaed2xpHsD9ESMhqIYd0nLMwNLD69Npy4HI+N" crossorigin="anonymous">
    <link rel="stylesheet" href="{{ asset('assets/css/master.css') }}">
    <script type="module" src="https://unpkg.com/ionicons@6.0.3/dist/ionicons/ionicons.esm.js"></script>
    <script nomodule src="https://unpkg.com/ionicons@6.0.3/dist/ionicons/ionicons.js"></script>
    @laravelPWA
</head>

<body>
    <nav class="nonagon-nav nav">
        <div class="logo-box">
            <img src="{{ asset('assets/img/logo-dark.svg') }}" alt="nonagon logo">
        </div>
        <div class="search-box">
            <form action="#" method="post">
                <div class="input-group mb-3 qd-search">
                    <div class="input-group-prepend">
                        <span class="input-group-text">
                            <ion-icon name="search-outline"></ion-icon>
                        </span>
                    </div>
                    <input type="text" id="search-form" placeholder="Search over 22,000 equipments"
                        class="form-control" aria-label="Amount (to the nearest dollar)">
                    <div class="input-group-append">
                        <span class="input-group-text">
                            <ion-icon name="funnel-outline"></ion-icon>
                        </span>
                    </div>
                </div>
            </form>
        </div>
        <div class="sub-controls">
            <a href="#">
                <ion-icon name="headset-outline"></ion-icon>
            </a>
            <a href="#">
                <ion-icon name="notifications-outline"></ion-icon>
            </a>
            <div class="user-actions">
                <img src="{{ asset('assets/img/user.jpg') }}" alt="user image">
                <div class="dropdown-action">
                    <h6>Tee Godwin</h6>
                    <ion-icon name="chevron-down-outline"></ion-icon>
                </div>
            </div>
            <div class="action-drop"></div>
        </div>
    </nav>

    <section class="hero-section">
        <div class="col-lg-12">
            <div class="row">
                <div class="col-lg-6">
                    <div class="hero-describe">
                        <h1>rent industrial equipment anywhere in africa</h1>
                        <h6>Secure, insured, and verified equipment rental platform for
                            construction, mining, and industrial projects
                        </h6>

                        <div class="hero-buttons">
                            <a href="#" class="btn btn-nonagon active">Explore Marketplace 
                                <ion-icon name="arrow-forward-outline" class="ml-2 icon"></ion-icon>
                            </a>
                            <a href="#" class="btn btn-nonagon">List your Equipment</a>
                        </div>

                        <form action="#">
                            <div class="search-area">
                                <div class="input-group mb-3">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text">
                                            <ion-icon name="search-outline"></ion-icon>
                                        </span>
                                    </div>
                                    <div class="custom-file">
                                        <input type="text" class="form-control" placeholder="Search over 22,000 equipments">
                                    </div>
                                </div>
                                <button class="search-action">
                                    <ion-icon name="arrow-forward-outline"></ion-icon>
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
                <div class="col-lg-6">
                    <div class="hero-section-img">
                        <img src="{{ asset('assets/img/truck.png') }}" alt="truck image">
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section class="sponsors-parent">
        <h2 class="backing-title">Backed by:</h2>
        
        <div class="sponsors-wrapper">
            <div class="sponsor-overlay left"></div>
            <div class="sponsor-overlay right"></div>
            
            <div class="sponsors-row slide-right">
                <div class="logo-item across">
                    <div class="logo-text">ACROSS</div>
                </div>
                <div class="logo-item power">
                    <div class="logo-text">A★ POWER</div>
                </div>
                <div class="logo-item prodigy">
                    <div class="logo-text">Prodigy</div>
                </div>
                <div class="logo-item techstars">
                    <div class="logo-text">techstars</div>
                </div>
                <div class="logo-item stripe">
                    <div class="logo-text">stripe</div>
                </div>
                <!-- Duplicate for seamless loop -->
                <div class="logo-item across">
                    <div class="logo-text">ACROSS</div>
                </div>
                <div class="logo-item power">
                    <div class="logo-text">A★ POWER</div>
                </div>
                <div class="logo-item prodigy">
                    <div class="logo-text">Prodigy</div>
                </div>
                <div class="logo-item techstars">
                    <div class="logo-text">techstars</div>
                </div>
                <div class="logo-item stripe">
                    <div class="logo-text">stripe</div>
                </div>
            </div>
            
            <!-- Second row sliding left -->
            <div class="sponsors-row slide-left">
                <div class="logo-item orbit">
                    <div class="logo-text">ORBIT</div>
                </div>
                <div class="logo-item verto">
                    <div class="logo-text">verto</div>
                </div>
                <div class="logo-item paystack">
                    <div class="logo-text">paystack</div>
                </div>
                <div class="logo-item smile">
                    <div class="logo-text">Smile</div>
                </div>
                <!-- Duplicate for seamless loop -->
                <div class="logo-item orbit">
                    <div class="logo-text">ORBIT</div>
                </div>
                <div class="logo-item verto">
                    <div class="logo-text">verto</div>
                </div>
                <div class="logo-item paystack">
                    <div class="logo-text">paystack</div>
                </div>
                <div class="logo-item smile">
                    <div class="logo-text">Smile</div>
                </div>
            </div>
        </div>
    </section>

    <script src="https://cdn.jsdelivr.net/npm/jquery@3.5.1/dist/jquery.slim.min.js"
        integrity="sha384-DfXdz2htPH0lsSSs5nCTpuj/zy4C+OGpamoFVy38MVBnE+IbbVYUew+OrCXaRkfj" crossorigin="anonymous">
    </script>
    <script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.1/dist/umd/popper.min.js"
        integrity="sha384-9/reFTGAW83EW2RDu2S0VKaIzap3H66lZH81PoYlFhbGU+6BZp6G7niu735Sk7lN" crossorigin="anonymous">
    </script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.min.js"
        integrity="sha384-+sLIOodYLS7CIrQpBjl+C7nPvqq+FbNUBDunl/OZv93DB7Ln/533i8e/mZXLi/P+" crossorigin="anonymous">
    </script>
</body>

</html>
