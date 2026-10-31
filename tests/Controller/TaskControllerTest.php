<?php

namespace App\Tests\Controller;

use App\Entity\Task;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class TaskControllerTest extends WebTestCase
{
    private KernelBrowser $client;

    public function setUp(): void
    {
        $this->client = static::createClient();

        static::getContainer()->get(EntityManagerInterface::class)
            ->createQuery('DELETE FROM ' . Task::class)
            ->execute();
    }

    public function testCreateTaskReturns201WithDefaults(): void
    {
        $this->client->jsonRequest('POST', '/api/tasks', ['title' => 'Renew licence']);

        $this->assertResponseStatusCodeSame(201);
        $task = $this->json();
        $this->assertSame('Renew licence', $task['title']);
        $this->assertSame('todo', $task['status']);
        $this->assertNull($task['due_date']);
        $this->assertArrayHasKey('created_at', $task);
    }

    public function testCreateTaskReturnsAllValidationErrors(): void
    {
        $this->client->jsonRequest('POST', '/api/tasks', [
            'title' => 'ab',
            'status' => 'foo',
            'due_date' => '2020-01-01',
        ]);

        $this->assertResponseStatusCodeSame(422);
        $errors = $this->json()['errors'];
        $this->assertArrayHasKey('title', $errors);
        $this->assertArrayHasKey('status', $errors);
        $this->assertArrayHasKey('due_date', $errors);
    }

    public function testListFiltersAndSorts(): void
    {
        $this->createTask('No due date');
        $this->createTask('Due later', dueDate: '2099-06-01');
        $this->createTask('Due sooner', dueDate: '2099-01-01');
        $this->createTask('Done task', status: 'done', dueDate: '2099-01-01');

        $this->client->request('GET', '/api/tasks?status=todo');

        $this->assertResponseIsSuccessful();
        $titles = array_column($this->json()['tasks'], 'title');
        $this->assertSame(['No due date', 'Due sooner', 'Due later'], $titles);

        $this->client->request('GET', '/api/tasks?due_before=2099-03-01');
        $titles = array_column($this->json()['tasks'], 'title');
        $this->assertSame(['Done task', 'Due sooner'], $titles);
    }

    public function testUpdateStatus(): void
    {
        $id = $this->createTask('Patch me')['id'];

        $this->client->jsonRequest('PATCH', "/api/tasks/$id/status", ['status' => 'done']);

        $this->assertResponseIsSuccessful();
        $this->assertSame('done', $this->json()['status']);
    }

    public function testUpdateStatusReturns404ForMissingTask(): void
    {
        $this->client->jsonRequest('PATCH', '/api/tasks/999999/status', ['status' => 'done']);

        $this->assertResponseStatusCodeSame(404);
        $this->assertArrayHasKey('id', $this->json()['errors']);
    }

    public function testUpdateStatusReturns422ForInvalidStatus(): void
    {
        $id = $this->createTask('Patch me')['id'];

        $this->client->jsonRequest('PATCH', "/api/tasks/$id/status", ['status' => 'foo']);

        $this->assertResponseStatusCodeSame(422);
        $this->assertArrayHasKey('status', $this->json()['errors']);
    }

    private function createTask(string $title, string $status = 'todo', ?string $dueDate = null): array
    {
        $this->client->jsonRequest('POST', '/api/tasks', array_filter([
            'title' => $title,
            'status' => $status,
            'due_date' => $dueDate,
        ]));

        return $this->json();
    }

    private function json(): array
    {
        return json_decode($this->client->getResponse()->getContent(), true);
    }
}
