$(function () {
  $('table [data-toggle="tooltip"]').tooltip();

  if ($(window).height() < $(document).height() && $(window).width() > 720) {
    $('.btn-top-page').attr('style', 'display: inline-block !important');
  }
});

function setButtonLink(selectObj, btnLink, defaultLink) {
  let link = '/portal/phishing-' + defaultLink;
  let id = selectObj.options[selectObj.selectedIndex].value;

  document.querySelector(btnLink).href = ((id > 0) ? link + '/preview/' + id : link);
}

function checkSameCheckboxes(mail, checkedState) {
  let sameCheckboxes = document.querySelectorAll('.modal-body input[value="' + mail + '"]');

  sameCheckboxes.forEach(function(checkbox) {
    checkbox.checked = checkedState;
  });
}

function insertEmails(textareaSelector) {
  let recipientsSeparator = "\n";
  let textareaRecipients = $(textareaSelector).val().split(recipientsSeparator);

  textareaRecipients.forEach(function(recipient) {
    let index = textareaRecipients.indexOf("");

    if (index !== -1) {
      textareaRecipients.splice(index, 1);
    }
  });

  let uncheckedCheckboxes = document.querySelectorAll('.modal-body input[type=checkbox]');
  let checkedCheckboxes = document.querySelectorAll('.modal-body input[type=checkbox]:checked');

  uncheckedCheckboxes.forEach(function(recipient) {
    let index = textareaRecipients.indexOf(recipient.value);

    if (index !== -1) {
      textareaRecipients.splice(index, 1);
    }
  });

  checkedCheckboxes.forEach(function(recipient) {
    if (textareaRecipients.includes(recipient.value) === false && recipient.value.match(/\S+@\S+/g)) {
      textareaRecipients.push(recipient.value);
    }
  });

  $(textareaSelector).val(textareaRecipients.join(recipientsSeparator));
}

function getCountOfEmails(emailsList, countLabel) {
  let list = document.querySelector(emailsList);
  let countValidEmailsLabel = document.querySelector(countLabel);

  let countValidEmails = 0;

  if (list.value.length > 4) {
    let validEmails = list.value.match(/[^\s@]+@[^\s@]+\.[^\s@]+/g);

    if (validEmails) {
      countValidEmails = validEmails.length;
    }
  }

  countValidEmailsLabel.innerHTML = countValidEmails;
}

function markCheckboxes(cover = '') {
  if (cover && $(cover).hasClass('d-none')) {
    $(cover).toggleClass('d-none');
  }

  let checkboxes = $(cover + ' input[type=checkbox]');
  checkboxes.prop('checked', !checkboxes.prop('checked'));
}

function replaceVariable(selector, variable) {
  if (confirm('Opravdu chcete obsah pole nahradit touto proměnnou?')) {
    document.querySelector(selector).value = variable;
  }
}

$('#phishing-email-variables code').on('click', function() {
  let input = $('#phishing-email-body');
  let insertedVariable = $(this).attr('data-var');

  let cursorPos = input.prop('selectionStart');
  let v = input.val();
  let textBefore = v.substring(0, cursorPos);
  let textAfter  = v.substring(cursorPos, v.length);

  input.val(textBefore + insertedVariable + textAfter);

  setCaretToPos(input[0], cursorPos + insertedVariable.length);
});

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

/* https://stackoverflow.com/questions/499126/jquery-set-cursor-position-in-text-area */
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

function setCaretToPos(input, pos) {
  setSelectionRange(input, pos, pos);
}

function blurIdentity() {
  $('.identity').toggleClass('blur-text');
}

function exportChart(filename, chartName, link) {
  link.download = filename + '.png';
  link.href = document.getElementById(chartName).toDataURL('image/png', 1);
}