function markIndication(id) {
  let prefix = 'indication-';
  let selector = '#' + prefix + id;

  $('*[id^="' + prefix + '"]').removeClass('active');

  $(selector).toggleClass('active');
  $(selector + '-text').toggleClass('active');
}

$(document).on('click', '.anchor-link', function (event) {
  event.preventDefault();

  $('html, body').animate({
    scrollTop: $($.attr(this, 'href')).offset().top - 50
  }, 500);
});