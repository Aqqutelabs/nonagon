<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0,user-scalable=no">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Nonagon | Landing Page</title>
    <link rel="shortcut icon" href="{{ asset('assets/img/favicon.png') }}" type="image/x-icon">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link
        href="https://fonts.googleapis.com/css2?family=DM+Sans:ital,opsz,wght@0,9..40,100..1000;1,9..40,100..1000&display=swap"
        rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Braah+One&display=swap" rel="stylesheet">

    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css"
        integrity="sha384-xOolHFLEh07PJGoPkLv1IbcEPTNtaed2xpHsD9ESMhqIYd0nLMwNLD69Npy4HI+N" crossorigin="anonymous">
    <link rel="stylesheet" href="{{ asset('assets/css/master.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/responsive.css') }}">

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/OwlCarousel2/2.3.4/assets/owl.carousel.min.css">
    <link rel="stylesheet"
        href="https://cdnjs.cloudflare.com/ajax/libs/OwlCarousel2/2.3.4/assets/owl.theme.default.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/7.0.1/css/all.min.css">

    <script type="module" src="https://unpkg.com/ionicons@6.0.3/dist/ionicons/ionicons.esm.js"></script>
    <script nomodule src="https://unpkg.com/ionicons@6.0.3/dist/ionicons/ionicons.js"></script>
    @laravelPWA
</head>

<body>
    <nav class="nonagon-nav nav">
        <div class="logo-box">
            <img src="{{ asset('assets/img/logo-dark.svg') }}" alt="nonagon logo">
        </div>
        {{-- <div class="search-box">
            <form action="#" method="post">
                <div class="input-group mb-3 qd-search">
                    <div class="input-group-prepend">
                        <span class="input-group-text">
                            <ion-icon name="search-outline"></ion-icon>
                        </span>
                    </div>
                    <input type="text" id="search-form" placeholder="Search equipments"
                        class="form-control" aria-label="Amount (to the nearest dollar)">
                    <div class="input-group-append">
                        <span class="input-group-text">
                            <ion-icon name="funnel-outline"></ion-icon>
                        </span>
                    </div>
                </div>
            </form>
        </div> --}}
    </nav>

    <section class="hero-section">
        <div class="w-100 d-flex justify-content-center mt-3">
            <div class="connective">connectivity, secure, insured</div>
        </div>

        <h2 class="mt-4 need-title">
            need equipment for your <br> project? <span class="highlighted">get it fast & hassle-free</span>
        </h2>

        <p class="equip-para">
            Stop chasing unreliable suppliers. Submit one request and we'll connect you with <br> verified equipment
            owners near you so your project stays on track.
        </p>

        <div class="d-flex justify-content-center mt-4">
            <a href="https://forms.gle/2LJtTBtUSv1Fvubh8" class="get-equipped">Request equipment now <i
                    class="fa fa-angle-right"></i></a>
        </div>

    </section>

    <div class="count-section padded">
        <div class="col-lg-12">
            <div class="row">
                <div class="col-lg-4 col-md-4 col-sm-12">
                    <div class="count-parent">
                        <div class="d-flex justify-content-center">
                            <ion-icon name="people-outline" class="iconic-count"></ion-icon>
                        </div>

                        <h4 class="info-count">30%</h4>
                        <h5 class="verify-own">Cost Savings</h5>
                    </div>
                </div>
                <div class="col-lg-4 col-md-4 col-sm-12">

                    <div class="count-parent">
                        <div class="d-flex justify-content-center">
                            <ion-icon name="globe-outline" class="iconic-count"></ion-icon>
                        </div>

                        <h4 class="info-count">24hrs</h4>
                        <h5 class="verify-own">Avg. response time</h5>
                    </div>
                </div>
                <div class="col-lg-4 col-md-4 col-sm-12">
                    <div class="count-parent">
                        <div class="d-flex justify-content-center">
                            <ion-icon name="trending-up-outline" class="iconic-count"></ion-icon>
                        </div>

                        <h4 class="info-count">98%</h4>
                        <h5 class="verify-own">success rate</h5>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="how-it-works padded">
        <div class="d-flex justify-content-center">
            <h5 class="labeled-sect">how it works</h5>
        </div>
        <h6 class="journey-start text-center">start your journey <span class="highlighted">here</span></h6>
        <p class="sect-describe text-center">Get the equipment you need in three simple steps. No more endless phone
            calls or <br> unreliable suppliers</p>

        <div class="col-lg-12 numbered-parent">
            <div class="numbered">
                <div class="circled-no">1</div>
                <div class="circled-no">2</div>
                <div class="circled-no">3</div>
            </div>
        </div>

        <div class="mt-5 col-lg-12 journey-parent">
            <div class="row">
                <div class="col-lg-4 p-2">
                    <div class="journey-exp">
                        <img src="{{ asset('assets/img/frame/1.png') }}" alt="process 1">
                        <h6>submit your request</h6>
                        <p>Fill out a simple form in 30 seconds</p>
                    </div>
                </div>
                <div class="col-lg-4 p-2">
                    <div class="journey-exp">
                        <img src="{{ asset('assets/img/frame/2.png') }}" alt="process 1">
                        <h6>get matched</h6>
                        <p>Receive options from verified equipment owners</p>
                    </div>
                </div>
                <div class="col-lg-4 p-2">
                    <div class="journey-exp">
                        <img src="{{ asset('assets/img/frame/3.png') }}" alt="process 1">
                        <h6>get it delivered</h6>
                        <p>choose the best fit and keep your project moving</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="why-nonagon padded">
        <div class="col-lg-12">
            <div class="row">
                <div class="col-lg-6">
                    <h5 class="labeled-sect">how it works</h5>
                    <h6 class="journey-start">why <span class="highlighted">nonagon</span></h6>
                    <p class="sect-describe">We've built the most reliable equipment marketplace in Nigeria. Here's
                        what sets us apart</p>

                    <img src="{{ asset('assets/img/why/main.png') }}" alt="" class="why-img">
                    <img src="{{ asset('assets/img/why/small.png') }}" alt="" class="why-small-img">
                </div>
                <div class="col-lg-6">
                    <div class="why-features">
                        <ion-icon name="time-outline" class="icon-why"></ion-icon>
                        <div class="why-desc">
                            <h2>save time</h2>
                            <p>One request connects you to multiple suppliers instantly</p>
                        </div>
                    </div>
                    <div class="why-features">
                        <ion-icon name="checkmark-circle-outline" class="icon-why"></ion-icon>
                        <div class="why-desc">
                            <h2>stay on track</h2>
                            <p>Avoid costly delays with reliable delivery</p>
                        </div>
                    </div>
                    <div class="why-features">
                        <ion-icon name="shield-outline" class="icon-why"></ion-icon>
                        <div class="why-desc">
                            <h2>peace of mind</h2>
                            <p>We screen vendors</p>
                        </div>
                    </div>
                    <div class="why-features">
                        <ion-icon name="flash-outline" class="icon-why"></ion-icon>
                        <div class="why-desc">
                            <h2>future ready</h2>
                            <p>Insurance and model contracts coming soon</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="trust-us padded" style="display: none">
        <div class="col-lg-12">
            <div class="row">
                <div class="col-lg-12">
                    <div class="d-flex justify-content-center">
                        <h5 class="labeled-sect">testimonials</h5>
                    </div>
                    <h6 class="journey-start text-center">clients trust <span class="highlighted">us</span></h6>
                    <p class="sect-describe text-center">Join thousands of project managers who rely on our verified
                        network for their equipment needs.</p>

                    <h6 class="text-center quoted">
                        <img src="{{ asset('images/icon/quote.png') }}">
                    </h6>

                    <div class="owl-carousel owl-theme testimonial-carousel">
                        <div class="item central-testimony">
                            <div class="testimonial-box">
                                <p>We secured the right excavator in less than 24 hours. Smooth process!</p>
                                <div class="d-flex justify-content-center starred">
                                    <i class="fa fa-star"></i>
                                    <i class="fa fa-star"></i>
                                    <i class="fa fa-star"></i>
                                    <i class="fa fa-star"></i>
                                    <i class="fa fa-star-half"></i>
                                </div>
                                <h6 class="text-center pos-quip">Project Manager, Lagos Construction</h6>
                            </div>
                        </div>

                        <div class="item central-testimony">
                            <div class="testimonial-box">
                                <p>We secured the right excavator in less than 24 hours. Smooth process!</p>
                                <div class="d-flex justify-content-center starred">
                                    <i class="fa fa-star"></i>
                                    <i class="fa fa-star"></i>
                                    <i class="fa fa-star"></i>
                                    <i class="fa fa-star"></i>
                                    <i class="fa fa-star-half"></i>
                                </div>
                                <h6 class="text-center pos-quip">Project Manager, Lagos Construction</h6>
                            </div>
                        </div>

                        <div class="item central-testimony">
                            <div class="testimonial-box">
                                <p>We secured the right excavator in less than 24 hours. Smooth process!</p>
                                <div class="d-flex justify-content-center starred">
                                    <i class="fa fa-star"></i>
                                    <i class="fa fa-star"></i>
                                    <i class="fa fa-star"></i>
                                    <i class="fa fa-star-half"></i>
                                </div>
                                <h6 class="text-center pos-quip">Project Manager, Lagos Construction</h6>
                            </div>
                        </div>

                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="trusted padded">
        <h6 class="trusted-title">Our trusted equipment brands</h6>
        <div class="logo-trusted">
            <div class="marquee">
                <div class="marquee-track">
                    <img src="{{ asset('assets/img/companies/1.png') }}" alt="">
                    <img src="{{ asset('assets/img/companies/2.png') }}" alt="">
                    <img src="{{ asset('assets/img/companies/3.png') }}" alt="">
                    <img src="{{ asset('assets/img/companies/4.png') }}" alt="">
                    <img src="{{ asset('assets/img/companies/5.png') }}" alt="">
                    <img src="{{ asset('assets/img/companies/6.png') }}" alt="">
                    <img src="{{ asset('assets/img/companies/7.png') }}" alt="">
                    <img src="{{ asset('assets/img/companies/8.png') }}" alt="">
                    <img src="{{ asset('assets/img/companies/9.png') }}" alt="">
                    <img src="{{ asset('assets/img/companies/10.png') }}" alt="">
                    <img src="{{ asset('assets/img/companies/11.png') }}" alt="">
                    <img src="{{ asset('assets/img/companies/12.png') }}" alt="">
                    <!-- duplicate for seamless scroll -->
                    <img src="{{ asset('assets/img/companies/1.png') }}" alt="">
                    <img src="{{ asset('assets/img/companies/2.png') }}" alt="">
                    <img src="{{ asset('assets/img/companies/3.png') }}" alt="">
                    <img src="{{ asset('assets/img/companies/4.png') }}" alt="">
                    <img src="{{ asset('assets/img/companies/5.png') }}" alt="">
                    <img src="{{ asset('assets/img/companies/6.png') }}" alt="">
                    <img src="{{ asset('assets/img/companies/7.png') }}" alt="">
                    <img src="{{ asset('assets/img/companies/8.png') }}" alt="">
                    <img src="{{ asset('assets/img/companies/9.png') }}" alt="">
                    <img src="{{ asset('assets/img/companies/10.png') }}" alt="">
                    <img src="{{ asset('assets/img/companies/11.png') }}" alt="">
                    <img src="{{ asset('assets/img/companies/12.png') }}" alt="">
                </div>
            </div>
        </div>
    </div>

    <div class="trust-us padded">
        <div class="d-flex justify-content-center mt-5 mb-4">
            <h5 class="labeled-sect">Start Now</h5>
        </div>

        <div class="equipment-grid">
            <div class="equipment-card">
                <div class="equipment-background equipment-bg-1"></div>
                <div class="equipment-content">
                    <h2 class="equipment-title">Get the right equipment, fast.</h2>
                    <p class="equipment-description">
                        Fill out a quick form and we'll connect you directly. no endless calls, no guesswork, just what you need when you need it.
                    </p>
                    <div class="equipment-footer">
                        <button class="equipment-action-btn">
                            <span class="equipment-play-icon"></span>
                            Request Equipment Now
                        </button>
                    </div>
                </div>
            </div>

            <!-- Card 2: Verified Owners -->
            <div class="equipment-card">
                <div class="equipment-background equipment-bg-2"></div>
                <div class="equipment-content">
                    <h2 class="equipment-title">Earn Commissions when Equipment gets hired.</h2>
                    <p class="equipment-description">
                        As Nonagon Agent, source-verified owners list their machines and earn an income. Simple, transparent, and rewarding—help businesses find equipment fast while you get paid effortlessly.
                    </p>
                    <div class="equipment-footer">
                        <button class="equipment-action-btn">
                            <span class="equipment-play-icon"></span>
                            Register as an agent
                        </button>
                    </div>
                </div>
            </div>

            <!-- Card 3: Perfect Match -->
            <div class="equipment-card">
                <div class="equipment-background equipment-bg-3"></div>
                <div class="equipment-content">
                    <h2 class="equipment-title">Make your equipment work for you.</h2>
                    <p class="equipment-description">
                        List your equipment once and earn every time it's hired—steady income, zero stress, no hassle.
                    </p>
                    <div class="equipment-footer">
                        <button class="equipment-action-btn">
                            <span class="equipment-play-icon"></span>
                            List your equipment
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="newsletter-parent padded mt-5 mb-5">    
        <div class="newsletter-signup-container">
            <!-- Main Content -->
            <div class="signup-content-wrapper">
                <h1 class="newsletter-headline">
                    Join our list and be the first to know when new equipment becomes available <span
                        class="highlight-text">near you.</span>
                </h1>

                <form class="email-signup-form" onsubmit="handleFormSubmit(event)">
                    <input type="email" class="email-input-field" placeholder="Enter your email address" required
                        aria-label="Email address" />
                    <button type="submit" class="signup-submit-btn">
                        Join the list
                        <span class="btn-icon">→</span>
                    </button>
                </form>
            </div>
        </div>
    </div>

    <footer class="footer">
        <div class="footer-container">
            <div class="footer-content">
                <!-- Brand Section -->
                <div class="footer-brand">
                    <div class="logo">
                        <img src="{{ asset('assets/img/logo-white.svg') }}" alt="">
                    </div>
                    <p class="brand-description">
                        Africa's leading platform for industrial equipment rental and management.
                    </p>
                    <div class="social-links">
                        <a href="#" class="social-link" aria-label="Facebook">
                            <i class="fab fa-facebook"></i>
                        </a>
                        <a href="https://www.instagram.com/nonagon?igsh=MWFqejdxcWEwaG01Yw%3D%3D&utm_source=qr"
                            class="social-link" aria-label="Instagram">
                            <i class="fab fa-instagram"></i>
                        </a>
                        <a href="#" class="social-link" aria-label="LinkedIn">
                            <i class="fab fa-linkedin"></i>
                        </a>
                        <a href="#" class="social-link" aria-label="Twitter">
                            <i class="fab fa-twitter"></i>
                        </a>
                    </div>
                </div>

                <!-- Quick Links -->
                <div class="footer-section">
                    <h3>Quick Links</h3>
                    <ul class="footer-links">
                        <li><a href="#">Company</a></li>
                        <li><a href="#">About</a></li>
                        <li><a href="#">Testimonial</a></li>
                        <li><a href="#">Blog</a></li>
                        <li><a href="#">Contact</a></li>
                    </ul>
                </div>

                <!-- Our Services -->
                <div class="footer-section">
                    <h3>Our Services</h3>
                    <ul class="footer-links">
                        <li><a href="#">Browse Equipment</a></li>
                        <li><a href="#">List Equipment</a></li>
                        <li><a href="#">How it works</a></li>
                        <li><a href="#">Pricing</a></li>
                        <li><a href="#">Safety Guidelines</a></li>
                    </ul>
                </div>

                <!-- Contact -->
                <div class="footer-section">
                    <h3>Contact</h3>
                    <ul class="contact-info">
                        <li>
                            <span class="contact-label">Phone:</span>
                            <a href="tel:025233126">+234 916 320 0787</a>
                        </li>
                        <li>
                            <span class="contact-label">Email:</span>
                            <a href="mailto:nonagon@gmail.com">nonagon@gmail.com</a>
                        </li>
                        <li>
                            <span class="contact-label">Address:</span>
                            <span>45 Orominieke street Dline</span>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/jquery@3.5.1/dist/jquery.slim.min.js"
        integrity="sha384-DfXdz2htPH0lsSSs5nCTpuj/zy4C+OGpamoFVy38MVBnE+IbbVYUew+OrCXaRkfj" crossorigin="anonymous">
    </script>
    <script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.1/dist/umd/popper.min.js"
        integrity="sha384-9/reFTGAW83EW2RDu2S0VKaIzap3H66lZH81PoYlFhbGU+6BZp6G7niu735Sk7lN" crossorigin="anonymous">
    </script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.min.js"
        integrity="sha384-+sLIOodYLS7CIrQpBjl+C7nPvqq+FbNUBDunl/OZv93DB7Ln/533i8e/mZXLi/P+" crossorigin="anonymous">
    </script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/OwlCarousel2/2.3.4/owl.carousel.min.js"></script>
    <script>
        $('.testimonial-carousel').owlCarousel({
            loop: true,
            margin: 10,
            nav: true,
            dots: false,
            responsive: {
                0: {
                    items: 1
                },
                600: {
                    items: 1
                },
                1000: {
                    items: 1
                }
            }
        })
    </script>
</body>

</html>
