<hr>

<form method="post" action="/portal/<?= $urlSection ?>">
  <input type="hidden" name="csrf-token" value="<?= $csrfToken ?>">

  <div class="container">
    <div class="row justify-content-center">
      <div class="col-lg-11">
        <div class="form-group mb-4">
          <div class="custom-control custom-checkbox my-1 mr-sm-2">
            <input type="checkbox" class="custom-control-input" id="<?= $formPrefix ?>recieve-email" name="<?= $formPrefix ?>recieve-email"<?= (($inputsValues['recieve-email']) ? ' checked' : ''); ?>>
            <label class="custom-control-label h5 font-weight-bold" for="<?= $formPrefix ?>recieve-email">Ano, chci se dobrovolně přihlásit k&nbsp;odebírání cvičných phishingových zpráv</label>
            <small class="form-text text-muted">
              To znamená, že několikrát do roka do mé e-mailové schránky dorazí e-mail, který bude obsahovat typické znaky phishingu a&nbsp;sociálního inženýrství. Na rozdíl od toho reálného mi ovšem ten cvičný nic neprovede ani neukradne.
            </small>
          </div>
        </div>

        <div class="form-group mb-4">
          <div class="custom-control custom-checkbox my-1 mr-sm-2">
            <input type="checkbox" class="custom-control-input" id="<?= $formPrefix ?>email-limit-checkbox" name="<?= $formPrefix ?>email-limit-checkbox"<?= (($inputsValues['email-limit-checkbox'] || $_inputsValues['email-limit'] !== NULL) ? ' checked' : ''); ?> onclick="$('#email-limit-count-wrapper').toggleClass('d-none');">
            <label class="custom-control-label" for="<?= $formPrefix ?>email-limit-checkbox">Omezit počet zpráv, které mi budou zaslány (nepovinné)</label>
          </div>
        </div>

        <div class="form-group mb-5<?php if (!($inputsValues['email-limit-checkbox'] || $_inputsValues['email-limit'] !== NULL)): ?> d-none<?php endif; ?>" id="email-limit-count-wrapper">
          <label for="<?= $formPrefix ?>email-limit">Zbývající počet cvičných phishingových zpráv, o&nbsp;které mám zájem</label>
          <input type="number" class="form-control" id="<?= $formPrefix ?>email-limit" name="<?= $formPrefix ?>email-limit" min="0" max="1000" value="<?= $inputsValues['email-limit'] ?>">
          <small class="form-text text-muted">Po odeslání každé zprávy dojde ke snížení tohoto čísla. Po dosažení nuly nebude zaslána žádná další zpráva, dokud toto číslo opět nezvýšíte.</small>
        </div>

        <div class="form-group text-center">
          <button type="submit" class="btn btn-primary btn-lg" name="<?= $formPrefix ?>">
            <span data-feather="save"></span>
            Změnit mé nastavení
          </button>
        </div>
      </div>
    </div>
  </div>
</form>