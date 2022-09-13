$(function () {
  $('table [data-toggle="tooltip"]').tooltip();

  if ($(window).height() < $(document).height() && $(window).width() > 720) {
    $('.btn-top-page').attr('style', 'display: inline-block !important');
  }
});

function setButtonLink(selectObj, btnLink, defaultLink) {
  var link = '/portal/phishing-' + defaultLink;
  var id = selectObj.options[selectObj.selectedIndex].value;

  document.querySelector(btnLink).href = ((id > 0) ? link + '/preview/' + id : link);
}

function checkSameCheckboxes(mail, checkedState) {
  var sameCheckboxes = document.querySelectorAll('.modal-body input[value="' + mail + '"]');

  sameCheckboxes.forEach(function(checkbox) {
    checkbox.checked = checkedState;
  });
}

function insertEmails(textareaSelector) {
  var recipientsSeparator = "\n";
  var textareaRecipients = $(textareaSelector).val().split(recipientsSeparator);

  textareaRecipients.forEach(function(recipient) {
    var index = textareaRecipients.indexOf("");

    if (index !== -1) {
      textareaRecipients.splice(index, 1);
    }
  });

  var uncheckedCheckboxes = document.querySelectorAll('.modal-body input[type=checkbox]');
  var checkedCheckboxes = document.querySelectorAll('.modal-body input[type=checkbox]:checked');

  uncheckedCheckboxes.forEach(function(recipient) {
    var index = textareaRecipients.indexOf(recipient.value);

    if (index !== -1) {
      textareaRecipients.splice(index, 1);
    }
  });

  checkedCheckboxes.forEach(function(recipient) {
    if (textareaRecipients.includes(recipient.value) === false) {
      textareaRecipients.push(recipient.value);
    }
  });

  $(textareaSelector).val(textareaRecipients.join(recipientsSeparator));
}

function getCountOfEmails(emailsList, countLabel) {
  var list = document.querySelector(emailsList);
  var count = document.querySelector(countLabel);

  count.innerHTML = ((list.value.length > 0) ? list.value.match(/\S+@\S+/g).length : 0);
}

function markCheckboxes(cover) {
  var checkBoxes = $(cover + ' input[type=checkbox]');
  checkBoxes.prop('checked', !checkBoxes.prop('checked'));
}

function replaceVariable(selector, variable) {
  if (confirm('Opravdu chcete obsah pole nahradit touto proměnnou?')) {
    document.querySelector(selector).value = variable;
  }
}

$('#phishing-email-variables code').on('click', function() {
  var input = $('#phishing-email-body');
  var insertedVariable = $(this).attr('data-var');

  var cursorPos = input.prop('selectionStart');
  var v = input.val();
  var textBefore = v.substring(0, cursorPos);
  var textAfter  = v.substring(cursorPos, v.length);

  input.val(textBefore + insertedVariable + textAfter);

  setCaretToPos(input[0], cursorPos + insertedVariable.length);
});

/* https://stackoverflow.com/questions/499126/jquery-set-cursor-position-in-text-area */
function setSelectionRange(input, selectionStart, selectionEnd) {
  if (input.setSelectionRange) {
    input.focus();
    input.setSelectionRange(selectionStart, selectionEnd);
  }
  else if (input.createTextRange) {
    var range = input.createTextRange();
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