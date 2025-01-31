$(function() {
  $('[data-toggle="tooltip"]').tooltip();

  if ($(window).height() + 50 < $(document).height() && $(window).width() >= 768) {
    $('.btn-top-page').toggleClass('d-none');
  }
});


$('.btn-close').on('click', function() {
  window.close();
});

$('.btn-confirm').on('click', function() {
  let message = $(this).data('confirm');

  if (message) {
    if (!confirm(message)) {
      return false;
    }
  }
});

$('.btn-submit').on('change', function() {
  $($(this).data('form')).submit();
});

$('.btn-redirect').on('change', function() {
  window.location.href = $(this).data('link') + $(this).val();
});

$('.btn-toggle-display').on('click', function() {
  $($(this).data('toggle')).toggleClass('d-none');
});


// PHISHING CAMPAIGNS
function extractEmails(emails, separator) {
  return emails.split(separator).map(item => item.trim().toLowerCase()).filter(Boolean);
}

function isEmailValid(input) {
  const validEmailPattern = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;

  return validEmailPattern.test(input);
}

$('.set-preview-btn').on('change', function() {
  let baseLink = $(this).data('preview-link');
  let id = $(this).find(':selected').val();
  let link = (id > 0) ? baseLink + '/preview/' + id : baseLink;

  $($(this).data('preview-btn')).attr('href', link);
});

$('.insert-recipients-emails').on('click', function() {
  const recipientsSeparator = "\n";
  let recipientsTextarea = $('#campaign-recipients');
  let recipientsCheckboxes = $('.modal-body input[type=checkbox]');
  let recipientsList = new Set(extractEmails(recipientsTextarea.val(), recipientsSeparator));

  recipientsCheckboxes.each(function() {
    let recipientEmail = $(this).val().toLowerCase();

    if (this.checked && isEmailValid(recipientEmail)) {
      recipientsList.add(recipientEmail);
    }
    else {
      recipientsList.delete(recipientEmail);
    }
  });

  recipientsTextarea.val(Array.from(recipientsList).join(recipientsSeparator));

  updateSumEmails();
});

$('#campaign-recipients').on('change keyup', function() {
  const recipientsSeparator = "\n";
  let recipientsList = $(this).val().split(recipientsSeparator);

  $('.modal-body input[type=checkbox]').prop('checked', false);

  recipientsList.forEach(function (recipient) {
    if (isEmailValid(recipient)) {
      let recipientCheckbox = $('.modal-body input[value="' + recipient + '" i]');

      if (recipientCheckbox.length) {
        recipientCheckbox.prop('checked', true);
        markSameCheckboxes(recipientCheckbox);
      }
    }

    updateSumEmails();
  });
});

function updateSumEmails() {
  const validEmailPattern = /[^\s@]+@[^\s@]+\.[^\s@]+/g;

  let recipientsTextarea = $('#campaign-recipients').val();
  let sumValidEmailsLabel = $('#countRecipients');

  let validEmails = recipientsTextarea.match(validEmailPattern);
  let sumValidEmails = validEmails ? validEmails.length : 0;

  sumValidEmailsLabel.text(sumValidEmails);
}

$('.mark-checkboxes').on('click', function() {
  let recipientsGroup = $(this).data('checkboxes-group');
  let checkboxes = $(recipientsGroup + ' input[type=checkbox]');
  let sumChecked = 0;

  if (recipientsGroup.length && $(recipientsGroup).hasClass('d-none')) {
    $(recipientsGroup).toggleClass('d-none');
  }

  checkboxes.prop('checked', !checkboxes.prop('checked'));
  checkboxes.each(function() {
    if (this.checked) {
      sumChecked++;
    }

    markSameCheckboxes($(this));
  });

  if (sumChecked === 0) {
    $(this).prop('checked', false);
  }
});

$('.mark-same-checkboxes').on('click', function() {
  markSameCheckboxes($(this));
});

function markSameCheckboxes(recipient) {
  $('.modal-body input[type=checkbox]').filter(function() {
    return $(this).val().toLowerCase() === recipient.val().toLowerCase();
  }).prop('checked', recipient.prop('checked'));
}


$('.expand-all-groups').on('click', function() {
  let isPressed = $(this).attr('aria-pressed') === 'true';

  $('.group-recipients').toggleClass('d-none', isPressed);
  $(this).attr('aria-pressed', !isPressed);
});

$('.import-recipients').on('click', function() {
  const recipientsSeparator = "\n";
  let recipientsTextarea = $('#campaign-recipients');
  let recipientsList = new Set(extractEmails(recipientsTextarea.val(), recipientsSeparator));
  let importFileInput = document.createElement('input');

  importFileInput.setAttribute('type', 'file');
  importFileInput.onchange = _ => {
    let file = importFileInput.files[0];

    if (file && (file.type === 'text/plain' || file.type === 'text/csv')) {
      let fileReader = new FileReader();

      fileReader.readAsText(file);
      fileReader.onload = function() {
        let importedRecipients = new Set(extractEmails(fileReader.result, /\r?\n/));
        let importedValidRecipients = [];

        if (file.type === 'text/csv') {
          importedRecipients = importValuesFromCSV(Array.from(importedRecipients));
        }

        importedRecipients.forEach(function (recipient) {
          if (isEmailValid(recipient)) {
            let recipientCheckbox = $('.modal-body input[value="' + recipient + '" i]');

            if (recipientCheckbox.length) {
              recipientCheckbox.prop('checked', true);
              markSameCheckboxes(recipientCheckbox);
            }

            importedValidRecipients.push(recipient);
          }
        });

        let allUniqRecipients = [...new Set([...recipientsList, ...importedValidRecipients])];

        recipientsTextarea.val(allUniqRecipients.join(recipientsSeparator));

        updateSumEmails();
      }
    }
    else {
      alert('Vybraný typ souboru není podporován.');
    }
  };

  importFileInput.click();
});

function importValuesFromCSV(lines) {
  let separator = prompt('Zadejte znak, který je v CSV souboru použit jako oddělovač hodnot.', ';');
  let values = [];

  if (separator !== null && separator !== '') {
    values = lines.flatMap(line => line.split(separator));
  }

  return values;
}

$('#blur-identities').on('click', function() {
  let form = $('#' + $(this).data('form'));

  $('.identity').toggleClass('blur-text');
  $.post(form.attr('action'), form.serialize());
});

$('.export-chart').on('click', function() {
  $(this).attr('download', $(this).data('filename') + '.png');
  $(this).attr('href', ($($(this).data('chart'))[0]).toDataURL('image/png', 1));
});


// VARIABLES
$('.replace-variable').on('click', function() {
  let input = $($(this).data('input'));

  if (confirm('Opravdu chcete obsah pole nahradit touto proměnnou?')) {
    input.val($(this).data('var'));
    input.focus();
  }
});

$('.insert-variable').on('click', function() {
  let input = $($(this).data('input'));

  input.val(input.val() + $(this).data('var'));
  input.focus();
});


// PHISHING EMAILS
$('.phishing-email-variables code').on('click', function() {
  let input = $('#phishing-email-body');
  let inputValue = input.val();
  let insertedValue = $(this).data('var');

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
  let urlInput = $('#phishing-website-url');

  urlInput.val($(this).data('var'));
  urlInput.focus();
});

$('.phishing-domain-protocol').on('click', function() {
  let urlInput = $('#phishing-website-url');
  let url = urlInput.val().trim();
  let selectedProtocol = $(this).data('var');

  if (url.startsWith('http://') && selectedProtocol === 'https') {
    url = url.replace('http://', 'https://');
  }
  else if (url.startsWith('https://') && selectedProtocol === 'http') {
    url = url.replace('https://', 'http://');
  }
  else if (!url.startsWith('http://') && !url.startsWith('https://')) {
    url = selectedProtocol + '://' + url;
  }

  urlInput.val(url);
  urlInput.focus();
});


// USER GROUPS
$('.user-groups-role').on('change', function() {
  let groups = $('#groups');
  let role = parseInt($(this).val());

  if (!isNaN(role) && (role === 1 || role === 2)) {
    groups.removeClass('d-none');
  }
  else {
    groups.addClass('d-none');
  }
});