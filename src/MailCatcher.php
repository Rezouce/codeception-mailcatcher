<?php
namespace Codeception\Module;

use Codeception\Module;
use Codeception\TestCase;
use GuzzleHttp\Client;
use MailCatcher\Mail;
use MailCatcher\MailCatcherAdapter;
use MailCatcher\MailCollection;

class MailCatcher extends Module
{

    private $url = '127.0.0.1';

    private $port = '1080';

    private $resetBeforeEachTest = false;

    private $mailCatcher;

    private $emails;

    /**
     * Initialize from configuration.
     */
    public function _initialize()
    {
        if (isset($this->config['fromEnv']) && $this->config['fromEnv']) {
            $this->loadFromEnv();
        } else {
            $this->loadFromConfig();
        }

        if (isset($this->config['resetBeforeEachTest'])) {
            $this->resetBeforeEachTest = $this->config['resetBeforeEachTest'];
        }
    }

    private function loadFromEnv()
    {
        if (isset($this->config['url'])) {
            $this->url = trim(getenv($this->config['url']), '/');
        }

        if (isset($this->config['port'])) {
            $this->port = getenv($this->config['port']);
        }
    }

    private function loadFromConfig()
    {
        if (isset($this->config['url'])) {
            $this->url = trim($this->config['url'], '/');
        }

        if (isset($this->config['port'])) {
            $this->port = $this->config['port'];
        }
    }

    /**
     * Drop the previous email from MailCatcher if not disabled by configuration.
     *
     * @param TestCase $test
     */
    public function _before(TestCase $test)
    {
        $this->emails = null;

        if ($this->resetBeforeEachTest) {
            $this->removeEmails();
        }
    }

    /**
     * Guzzle client to talk with MailCatcher.
     *
     * @return \MailCatcher\MailCatcher
     */
    private function mailCatcher()
    {
        if (null === $this->mailCatcher) {
            $this->mailCatcher = new \MailCatcher\MailCatcher(
                new MailCatcherAdapter(new Client(['base_uri' => $this->url . ':' . $this->port]))
            );
        }

        return $this->mailCatcher;
    }

    /**
     * Get all messages from MailCatcher.
     *
     * @return MailCollection
     */
    private function emails()
    {
        if (null === $this->emails) {
            $this->emails = $this->mailCatcher()->messages();
        }

        return $this->emails;
    }

    /**
     * Remove all emails in MailCatcher.
     */
    public function removeEmails()
    {
        $this->mailCatcher()->removeMessages();
    }

    /**
     * Assert that there is at least one email.
     * @param MailCollection $emails
     */
    public function hasEmails(MailCollection $emails = null)
    {
        if (null === $emails) {
            $emails = $this->emails();
        }

        $this->assertNotEmpty($emails->count(), 'Has an email');
    }

    /**
     * Assert the number of emails sent.
     *
     * @param int $count
     * @param MailCollection $emails
     */
    public function seeNumberEmails($count, MailCollection $emails = null)
    {
        if (null === $emails) {
            $emails = $this->emails();
        }

        $this->assertEquals($count, $emails->count(), 'Number of emails');
    }

    /**
     * Assert that at least an email contains some text.
     *
     * @param string $expected
     * @param MailCollection $emails
     */
    public function seeInEmail($expected, MailCollection $emails = null)
    {
        if (null === $emails) {
            $emails = $this->emails();
        }

        $emails = $emails->filter(function(Mail $mail) use ($expected) {
            $content = $mail->subject()
                . $mail->sender()
                . implode(' ', $mail->recipients())
                . ($mail->hasText() ? $mail->text() : '')
                . ($mail->hasHtml() ? $mail->html() : '');

            return false !== strstr(str_replace("=\r\n", '', $content), $expected);
        });

        $this->assertNotEmpty($emails->count(), 'Email contains');
    }

    /**
     * Get all emails with the given subject.
     *
     * @param string $subject
     * @param bool $strict
     * @return MailCollection
     */
    public function getEmailsBySubject($subject, $strict = false)
    {
        return $this->emails()->filter(function(Mail $mail) use ($subject, $strict) {
            return $strict
                ? $subject === $mail->subject()
                : false !== strstr($mail->subject(), $subject);
        });
    }

    /**
     * Get all emails with the given sender.
     *
     * @param string $sender
     * @param bool $strict
     * @return MailCollection
     */
    public function getEmailsBySender($sender, $strict = false)
    {
        return $this->emails()->filter(function(Mail $mail) use ($sender, $strict) {
            return $strict
                ? $sender === $mail->sender()
                : false !== strstr($mail->sender(), $sender);
        });
    }

    /**
     * Get all emails with the given recipients.
     *
     * @param array $recipients
     * @param bool $strict
     * @return MailCollection
     */
    public function getEmailsByRecipients(array $recipients, $strict = false)
    {
        return $this->emails()->filter(function(Mail $mail) use ($recipients, $strict) {
            $found = count(array_intersect($recipients, $mail->recipients()));

            return $strict
                ? $found === count($recipients)
                : $found > 0;
        });
    }
}
