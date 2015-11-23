<?php


class MailCatcherTest extends \Codeception\TestCase\Test
{
    /**
     * @var \FunctionalTester
     */
    protected $tester;

    protected function _before()
    {
//        $this->removeEmails();
    }

    /** @test */
    public function it_count_the_emails()
    {
        mail('user@example.com', 'Subject', 'Body.');
        mail('user@example.com', 'Subject', 'Body.');
        mail('user@example.com', 'Subject', 'Body.');

        $this->seeNumberEmails(3);
    }

    /** @test */
    public function it_remove_the_emails()
    {
        mail('user@example.com', 'Subject', 'Body.');
        $this->seeNumberEmails(1);

        $this->removeEmails();
        $this->seeNumberEmails(0);
    }

    /** @test */
    public function it_has_emails()
    {
        mail('user@example.com', 'Subject', 'Body.');

        $this->seeHasEmails();
    }

    /** @test */
    public function it_has_emails_which_contain_a_stringin_its_source()
    {
        mail('user@example.com', 'Subject', 'Body.');

        $this->seeInEmail('example');
    }

    /** @test */
    public function it_retrieve_the_emails_by_their_subject()
    {
        mail('user@example.com', 'Subject 1', 'Body.');
        mail('user@example.com', 'Subject 2', 'Body.');
        mail('user@example.com', 'Other name', 'Body.');

        $this->assertEquals(2, $this->getEmailsBySubject('Subject')->count());
    }

    /** @test */
    public function it_retrieve_the_emails_by_their_subject_strictly()
    {
        mail('user@example.com', 'Subject 1', 'Body.');
        mail('user@example.com', 'Subject 2', 'Body.');
        mail('user@example.com', 'Subject', 'Body.');

        $this->assertEquals(1, $this->getEmailsBySubject('Subject', true)->count());
    }

    /** @test */
    public function it_retrieve_the_emails_by_their_sender()
    {
        mail('user@example.com', 'Subject', 'Body.', 'From: user@example.com');
        mail('user@example.com', 'Subject', 'Body.', 'From: user@example.com');
        mail('user@example.com', 'Subject', 'Body.', 'From: user@other.com');

        $this->assertEquals(2, $this->getEmailsBySender('example.com')->count());
    }

    /** @test */
    public function it_retrieve_the_emails_by_their_sender_strictly()
    {
        mail('user@example.com', 'Subject', 'Body.', 'From: user@example.coma');
        mail('user@example.com', 'Subject', 'Body.', 'From: user@example.comb');
        mail('user@example.com', 'Subject', 'Body.', 'From: user@example.com');

        $this->assertEquals(1, $this->getEmailsBySender('user@example.com', true)->count());
    }

    /** @test */
    public function it_retrieve_the_emails_by_their_recipients()
    {
        mail('user1@example.com, user2@example.com, user3@example.com', 'Subject', 'Body.');
        mail('user1@example.com, user4@example.com', 'Subject', 'Body.');
        mail('user4@example.com', 'Subject', 'Body.');

        $this->assertEquals(2, $this->getEmailsByRecipients(['user1@example.com', 'user2@example.com'])->count());
    }

    /** @test */
    public function it_retrieve_the_emails_by_their_recipients_strictly()
    {
        mail('user1@example.com, user2@example.com, user3@example.com', 'Subject', 'Body.');
        mail('user1@example.com, user2@example.com', 'Subject', 'Body.');

        $this->assertEquals(1, $this->getEmailsByRecipients(['user1@example.com', 'user2@example.com'], true)->count());
    }

    /** @test */
    public function it_manage_special_chars()
    {
        mail('user3@example.com', 'éàù', 'ïËù');

        $this->assertEquals(1, $this->getEmailsBySubject('éàù')->count());
        $this->assertEquals(1, $this->seeInEmail('ïËù')->count());
    }
}
