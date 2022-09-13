<?php
  use PHPUnit\Framework\TestCase;

  require_once '../globalFunctions.php';

  final class GlobalFunctionsTest extends TestCase {
    public function testGetEmailPartUsername(): void {
      $input = 'martin.sebela@cesnet.cz';

      $result = get_email_part($input, 'username');

      $this->assertSame('martin.sebela', $result);
    }

    public function testGetEmailPartDomain(): void {
      $input = 'martin.sebela@cesnet.cz';

      $result = get_email_part($input, 'domain');

      $this->assertSame('cesnet.cz', $result);
    }

    public function testGetEmailParts(): void {
      $input = 'martin.sebela@cesnet.cz';

      $parts = get_email_part($input);

      $this->assertSame('martin.sebela', $parts[0]);
      $this->assertSame('cesnet.cz', $parts[1]);
    }

    public function testGetEmailPartsFail(): void {
      $input = 'martin.sebela-cesnet.cz';

      $result = get_email_part($input);

      $this->assertNull($result);
    }

    public function testGetProtocolFromUrl(): void {
      $input = 'https://www.phishingator.cz';

      $result = get_protocol_from_url($input);

      $this->assertSame('https', $result);
    }

    public function testGetHostnameFromUrl(): void {
      $input = 'https://phishingator.cesnet.cz';

      $result = get_hostname_from_url($input);

      $this->assertSame('phishingator.cesnet.cz', $result);
    }

    public function testGetDomainFromUrl(): void {
      $input = 'https://phishingator.cesnet.cz';

      $result = get_domain_from_url($input);

      $this->assertSame('cesnet.cz', $result);
    }

    public function testNumberFromString(): void {
      $input = '256';

      $result = get_number_from_get_string($input);

      $this->assertSame(256, $result);
    }

    public function testGetFormattedNumber(): void {
      $input = 1024256;

      $result = get_formatted_number($input);

      $this->assertSame('1 024 256', $result);
    }

    public function testInsertNonBreakingSpaces(): void {
      $input = 'Lorem ipsum 1 024.';

      $result = insert_nonbreaking_spaces($input);

      $this->assertSame('Lorem&nbsp;ipsum&nbsp;1&nbsp;024.', $result);
    }
  }
