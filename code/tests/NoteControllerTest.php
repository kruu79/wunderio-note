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

        $this->requestNote($firstNoteId);
        $this->assertResponseIsSuccessful();
        $this->assertEquals($firstNoteTitle, $this->getResponseData()->note->title);
    }


    public function test_it_returns_404_when_note_does_not_exist(): void
    {
        $nonExistingNoteId = 999;

        $this->requestNote($nonExistingNoteId);
        $this->assertResponseStatusCodeSame(404);
        $this->assertEquals('fail', $this->getResponseStatus());
    }

    public function test_it_adds_a_new_note()
    {
        $title = 'Note Title';

        $this->client->xmlHttpRequest('POST', "/note/add", [
            'title' => $title,
            'text' => 'some text'
        ]);
        $this->assertResponseIsSuccessful();

        $noteId = $this->getResponseData()->note->id;
        $this->requestNote($noteId);
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
        $this->client->xmlHttpRequest('POST', "/note/add", [
            'title' => $title,
            'text' => $text
        ]);

        $this->assertEquals('fail', $this->getResponseStatus());
    }





    /**
     * KernelBrowser $client sends request to retrieve Note with $id.
     *
     * @param $id
     */
    private function requestNote($id): void
    {
        $this->client->xmlHttpRequest('GET', "/note/$id");
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
