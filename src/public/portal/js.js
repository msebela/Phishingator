$(function() {
  $('table [data-toggle="tooltip"]').tooltip();

  if ($(window).height() < $(document).height() && $(window).width() > 720) {
    $('.btn-top-page').attr('style', 'display: inline-block !important');
  }
});


$('.btn-close').on('click', function() {
  window.close();
});

$('.btn-confirm').on('click', function() {
  let message = $(this).attr('data-confirm');

  if (message) {
    if (!confirm(message)) {
      return false;
    }
  }
});

$('.btn-submit').on('change', function() {
  $($(this).attr('data-form')).submit();
});

$('.btn-redirect').on('change', function() {
  window.location.href = $(this).attr('data-link') + $(this).val();
});

$('.btn-toggle-display').on('click', function() {
  $($(this).attr('data-toggle')).toggleClass('d-none');
});


// PHISHING CAMPAIGNS
$('.set-preview-btn').on('change', function() {
  let link = $(this).attr('data-preview-link');
  let id = $(this).find(':selected').val();

  $($(this).attr('data-preview-btn')).attr('href', ((id > 0) ? link + '/preview/' + id : link));
});

$('.insert-recipients-emails').on('click', function() {
  let recipientsTextarea = $($(this).attr('data-recipients-textarea'));

  let recipientsSeparator = "\n";
  let recipientsList = recipientsTextarea.val().split(recipientsSeparator);

  recipientsList.forEach(function(recipient) {
    let index = recipientsList.indexOf("");

    if (index !== -1) {
      recipientsList.splice(index, 1);
    }
  });

  $('.modal-body input[type=checkbox]').each(function(recipient) {
    let index = recipientsList.indexOf($(this).val());

    if (index !== -1) {
      recipientsList.splice(index, 1);
    }
  });

  $('.modal-body input[type=checkbox]:checked').each(function(recipient) {
    if (recipientsList.includes($(this).val()) === false && $(this).val().match(/\S+@\S+/g)) {
      recipientsList.push($(this).val());
    }
  });

  recipientsTextarea.val(recipientsList.join(recipientsSeparator));
});

$('.get-sum-of-emails').on('click keyup', function() {
  let listEmails = $($(this).attr('data-recipients-textarea'));
  let sumValidEmailsLabel = $($(this).attr('data-sum-recipients-label'));

  let sumValidEmails = 0;

  if (listEmails.val().length > 4) {
    let validEmails = listEmails.val().match(/[^\s@]+@[^\s@]+\.[^\s@]+/g);

    if (validEmails) {
      sumValidEmails = validEmails.length;
    }
  }

  sumValidEmailsLabel.text(sumValidEmails);
});

$('.mark-checkboxes').on('click', function() {
  let group = $(this).attr('data-checkboxes-group');
  let checkboxes = $(group + ' input[type=checkbox]');

  if (group && $(group).hasClass('d-none')) {
    $(group).toggleClass('d-none');
  }

  checkboxes.prop('checked', !checkboxes.prop('checked'));
});

$('.mark-same-checkboxes').on('click', function() {
  $('.modal-body input[value="' + $(this).val() + '"]').prop('checked', $(this).prop('checked'));
});


$('#blur-identities').on('click', function() {
  $('.identity').toggleClass('blur-text');
});

$('.export-chart').on('click', function(e) {
  $(this).attr('download', $(this).attr('data-filename') + '.png');
  $(this).attr('href', ($($(this).attr('data-chart'))[0]).toDataURL('image/png', 1));
});


// VARIABLES
$('.replace-variable').on('click', function() {
  let input = $($(this).attr('data-input'));

  if (confirm('Opravdu chcete obsah pole nahradit touto proměnnou?')) {
    input.val($(this).attr('data-var'));
    input.focus();
  }
});

$('.insert-variable').on('click', function() {
  let input = $($(this).attr('data-input'));

  input.val(input.val() + $(this).attr('data-var'));
  input.focus();
});


// PHISHING EMAILS
$('#phishing-email-variables code').on('click', function() {
  let input = $('#phishing-email-body');
  let inputValue = input.val();
  let insertedValue = $(this).attr('data-var');

  let cursorPos = input.prop('selectionStart');
  let cursorPosAfter = cursorPos + insertedValue.length;

  let textBefore = inputValue.substring(0, cursorPos);
  let textAfter  = inputValue.substring(cursorPos, inputValue.length);

  input.val(textBefore + insertedValue + textAfter);

  setSelectionRange(input[0], cursorPosAfter, cursorPosAfter);
});

function setSelectionRange(input, selectionStart, selectionEnd) {
  if (input.setSelectionRange) {
    input.focus();
    input.setSelectionRange(selectionStart, selectionEnd);
  }
  else if (input.createTextRange) {
    let range = input.createTextRange();

    range.collapse(true);
    range.moveEnd('character', selectionEnd);
    range.moveStart('character', selectionStart);
    range.select();
  }
}


// PHISHING WEBSITES
$('#phishing-domains-dropdown a').on('click', function() {
  let domainInput = $('#phishing-website-url');

  domainInput.val($(this).attr('data-var'));
  domainInput.focus();
});

$('.phishing-domain-protocol').on('click', function() {
  let domainInput = $('#phishing-website-url');
  let domain = domainInput.val();

  let selectedProtocol = $(this).attr('data-var');
  let protocol = null;

  if (domain.indexOf('http:') >= 0 && selectedProtocol === 'https') {
    protocol = domain.replace('http:', 'https:');
  }
  else if (domain.indexOf('https:') >= 0 && selectedProtocol === 'http') {
    protocol = domain.replace('https:', 'http:');
  }
  else if (domain.indexOf('http:') === -1 && domain.indexOf('https:') === -1) {
    protocol = selectedProtocol + '://' + domain;
  }

  if (protocol !== null) {
    domainInput.val(protocol);
  }

  domainInput.focus();
});