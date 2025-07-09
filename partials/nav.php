<nav class="nav">
    <div class="container-lg d-flex align-items-center">
        <a href="/" class="nav-brand">Tute<span>.</span>io</a>
        <!-- Nav Mobile -->
        <div class="d-block d-lg-none flex-grow-1 d-flex justify-content-end nav-mobile">
            <button type="button" class="btn-custom ghost nav-mobile__btn" id="navMobileOpenBtn">
                <i class="bi bi-list"></i>
            </button>
            <div class="nav-mobile__menu-overlay hidden"></div>
            <div class="nav-mobile__menu" id="navMobileMenu">
                <button type="button" class="btn-custom ghost nav-mobile__btn" id="navMobileCloseBtn">
                    <i class="bi bi-x"></i>
                </button>
                <ul class="container-fluid px-0 py-3">
                    <li><a href="<?= APP_URL ?>/" class="nav-link-custom <?= trim($active_link) === "home" ? "active" : "" ?>">Home</a></li>
                    <li><a href="<?= APP_URL ?>/courses.php" class="nav-link-custom <?= trim($active_link) === "course" ? "active" : "" ?>">Courses</a></li>
                    <li><a href="<?= APP_URL ?>/enquiries.php" class="nav-link-custom <?= trim($active_link) === "enquiry" ? "active" : "" ?>">Enquiries</a></li>
                    <li><a href="<?= APP_URL ?>/user/profile.php" class="nav-link-custom <?= trim($active_link) === "user" ? "active" : "" ?>">User profile</a></li>
                    <li><a href="<?= APP_URL ?>/auth/logout.php" class="nav-link-custom">Log out</a></li>
                </ul>
            </div>
        </div>
        <!-- Nav Desktop -->
        <div class="d-lg-flex d-none flex-grow-1 align-items-center nav-desktop">

            <ul class="flex-grow-1 d-flex align-items-center justify-content-center m-0 nav-desktop__menu">
                <li><a href="<?= APP_URL ?>/" class="nav-link-custom <?= trim($active_link) === "home" ? "active" : "" ?>">Home</a></li>
                <li><a href="<?= APP_URL ?>/courses.php" class="nav-link-custom <?= trim($active_link) === "course" ? "active" : "" ?>">Courses</a></li>
                <li><a href="<?= APP_URL ?>/enquiries.php" class="nav-link-custom <?= trim($active_link) === "enquiry" ? "active" : "" ?>">Enquiries</a></li>
            </ul>

            <div class="dropdown">
                <button class="btn d-flex align-items-center" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                    <span class="rounded-circle d-flex align-items-center justify-content-center" style="background-color: var(--info-color); color:var(--surface-color); width:2.5rem; height:2.5rem;"><?= ucfirst($_SESSION["user"]["username"][0]) ?></span>
                </button>
                <ul class="dropdown-menu">

                    <li><a class="dropdown-item" href="<?= APP_URL ?>/user/profile.php">Profile</a></li>
                    <hr class="my-0">
                    <li><a class="dropdown-item" style="--bs-dropdown-link-color: var(--danger-color);" href="<?= APP_URL ?>/auth/logout.php">Log out</a></li>
                </ul>
            </div>
        </div>
    </div>
</nav>