<?php

namespace App\DataFixtures;

use App\Entity\Note;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class AppFixtures extends Fixture
{
    /**
     * @param ObjectManager $manager
     * @throws \Exception
     */
    public function load(ObjectManager $manager): void
    {
        $notes = [
            [
                'id' => 1,
                'title' => 'First Note',
                'text' => 'The text of the first note.',
                'age_in_days' => 3
            ],

            [
                'id' => 2,
                'title' => 'Second Note',
                'text' => 'The text of the first note.',
                'age_in_days' => 2
            ],

            [
                'title' => 'Third Note',
                'text' => 'The text of the first note.',
                'age_in_days' => 1,
                'id' => 3,
            ],
        ];

        foreach ($notes as $noteData) {
            $this->persistNote($noteData, $manager);
        }

        $manager->flush();
    }

    /**
     * Persists Note entity in $manager
     *
     * @param $noteData
     * @param ObjectManager $manager
     * @throws \Exception
     */
    private function persistNote($noteData, ObjectManager $manager)
    {
        $note = new Note();
        $note->setTitle($noteData['title']);
        $note->setText($noteData['text']);

        $date = (new \DateTimeImmutable())->modify(sprintf('-%d day', $noteData['age_in_days']));
        $note->setCreatedAt($date);

        $manager->persist($note);
    }
}
