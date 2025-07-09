// Open mobile menu
$("#navMobileOpenBtn").click(() => {
  $("#navMobileMenu").addClass("open");
  $(".nav-mobile__menu-overlay").removeClass("hidden");
});

// Close mobile menu
$("#navMobileCloseBtn").click(() => {
  $("#navMobileMenu").removeClass("open");
  $(".nav-mobile__menu-overlay").addClass("hidden");
});

