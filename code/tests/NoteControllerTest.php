<?php

namespace App\Tests;

use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class NoteControllerTest extends WebTestCase
{
    /** @var KernelBrowser $client */
    private $client;

    public function setUp(): void {
        $this->client = static::createClient();
    }


    public function test_it_returns_note_in_json_format(): void
    {
        // Defined in AppFixtures.php
        $firstNoteId = 1;
        $firstNoteTitle = 'First Note';

        $this->noteRequest($firstNoteId);
        $this->assertResponseIsSuccessful();
        $this->assertEquals($firstNoteTitle, $this->getResponseData()->note->title);
    }


    public function test_it_returns_404_when_note_does_not_exist(): void
    {
        $nonExistingNoteId = 999;

        $this->noteRequest($nonExistingNoteId);
        $this->assertResponseStatusCodeSame(404);
        $this->assertEquals('fail', $this->getResponseStatus());
    }


    public function test_it_adds_a_new_note()
    {
        $title = 'Note Title';

        $content = json_encode([
            'title' => $title,
            'text' => 'some text'
        ]);

        $this->client->xmlHttpRequest('POST', "/note/add", [], [], [], $content);
        $this->assertResponseIsSuccessful();

        $noteId = $this->getResponseData()->note->id;
        $this->noteRequest($noteId);
        $this->assertResponseIsSuccessful();

        $this->assertEquals($title, $this->getResponseData()->note->title);
    }


    public function invalidNoteInputDataProvider()
    {
        return [
            ['title' => '', 'text' => 'some text'],
            ['title' => 'some title', 'text' => ''],
            ['title' => '  ', 'text' => '  '],
            ['title' => 'some title', 'text' => null],
        ];
    }

    /**
     * @dataProvider invalidNoteInputDataProvider
     * @param $title
     * @param $text
     */
    public function test_it_fails_when_trying_to_create_note_with_invalid_input($title, $text)
    {
        $content = json_encode([
            'title' => $title,
            'text' => $text
        ]);

        $this->client->xmlHttpRequest('POST', "/note/add", [], [], [], $content);

        $this->assertEquals('fail', $this->getResponseStatus());
    }


    public function test_it_edits_existing_note()
    {
        $firstNoteId = 1;
        $editedTitle = 'New Title';
        $editedText = 'new text';

        $this->noteRequest($firstNoteId, 'PUT', [
            'title' => $editedTitle,
            'text' => $editedText
        ]);

        $this->assertResponseIsSuccessful();

        $this->noteRequest($firstNoteId);
        $this->assertResponseIsSuccessful();
        $this->assertEquals($editedTitle, $this->getResponseData()->note->title);
        $this->assertEquals($editedText, $this->getResponseData()->note->text);
    }


    public function test_it_fails_when_editing_note_with_invalid_input()
    {
        $firstNoteId = 1;

        $this->noteRequest($firstNoteId, 'PUT', [
            'title' => '',
            'text' => ''
        ]);

        $this->assertEquals('fail', $this->getResponseStatus());
    }


    public function test_it_can_delete_note(): void
    {
        $firstNoteId = 1;

        $this->noteRequest($firstNoteId, 'DELETE');
        $this->assertResponseIsSuccessful();

        $this->noteRequest($firstNoteId);
        $this->assertResponseStatusCodeSame(404);
    }


    public function test_it_returns_all_notes_ordered_by_newest()
    {
        // Defined in AppFixtures.php
        $oldestNoteId = 1;
        $newestNoteId = 3;

        $this->client->xmlHttpRequest('GET', "/notes");

        $this->assertEquals($newestNoteId, $this->getResponseData()->notes[0]->id);
        $this->assertEquals($oldestNoteId, end($this->getResponseData()->notes)->id);
    }


    public function test_it_returns_all_notes_ordered_by_oldest()
    {
        // Defined in AppFixtures.php
        $oldestNoteId = 1;
        $newestNoteId = 3;

        $this->client->xmlHttpRequest('GET', "/notes?sort=oldest");

        $this->assertEquals($oldestNoteId, $this->getResponseData()->notes[0]->id);
        $this->assertEquals($newestNoteId, end($this->getResponseData()->notes)->id);
    }


    public function test_it_returns_all_notes_limiting_result_count()
    {
        $noteCount = 2;

        $this->client->xmlHttpRequest('GET', "/notes?limit=$noteCount");

        $this->assertEquals($noteCount, count($this->getResponseData()->notes));
    }


    public function test_it_returns_all_notes_with_search_term_in_text()
    {
        $secondNoteId = 2;
        $secondNoteTextFragment = 'the second';

        $this->client->xmlHttpRequest('GET', "/notes?search=$secondNoteTextFragment");

        $this->assertEquals($secondNoteId, $this->getResponseData()->notes[0]->id);
    }


    /**
     * KernelBrowser $client sends request to retrieve Note with $id.
     *
     * @param $id
     * @param string $method
     * @param array $contentArray
     */
    private function noteRequest($id, $method = 'GET', $contentArray = array()): void
    {
        $content = json_encode($contentArray);
        $this->client->xmlHttpRequest($method, "/note/$id", [], [], [], $content);
    }

    /**
     * Returns data property of JSON response.
     *
     * @return \stdClass
     */
    private function getResponseData(): \stdClass
    {
        return $this->decodedResponse()->data;
    }

    /**
     * Returns status property of JSON response.
     *
     * @return string
     */
    private function getResponseStatus(): string
    {
        return $this->decodedResponse()->status;
    }

    /**
     * @return \stdClass
     */
    private function decodedResponse(): \stdClass
    {
        return json_decode($this->client->getResponse()->getContent());
    }

}
