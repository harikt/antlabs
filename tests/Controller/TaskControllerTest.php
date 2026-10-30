<?php

namespace App\Tests\Controller;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

final class TaskControllerTest extends WebTestCase
{
    private KernelBrowser $client;

    protected function setUp(): void
    {
        $this->client = static::createClient();
        static::getContainer()->get(EntityManagerInterface::class)
            ->getConnection()->executeStatement('DELETE FROM tasks');
    }

    public function testCreateReturns201WithDefaults(): void
    {
        $data = $this->request('POST', '/api/tasks', ['title' => 'Write README']);

        self::assertResponseStatusCodeSame(201);
        self::assertSame('Write README', $data['title']);
        self::assertSame('todo', $data['status']);
        self::assertNull($data['due_date']);
        self::assertArrayHasKey('created_at', $data);
        self::assertArrayHasKey('updated_at', $data);
    }

    public function testCreateStoresDueDateInUtc(): void
    {
        $data = $this->request('POST', '/api/tasks', ['title' => 'Demo', 'due_date' => '2099-12-01T10:00:00+05:30']);

        self::assertResponseStatusCodeSame(201);
        self::assertSame('2099-12-01T04:30:00+00:00', $data['due_date']);
    }

    public function testCreateValidatesInput(): void
    {
        $data = $this->request('POST', '/api/tasks', [
            'title' => 'ab',
            'status' => 'blocked',
            'due_date' => '2000-01-01T10:00:00+00:00',
        ]);

        self::assertResponseStatusCodeSame(422);
        self::assertArrayHasKey('title', $data['errors']);
        self::assertArrayHasKey('status', $data['errors']);
        self::assertArrayHasKey('due_date', $data['errors']);
    }

    public function testCreateRequiresTitle(): void
    {
        $data = $this->request('POST', '/api/tasks', []);

        self::assertResponseStatusCodeSame(422);
        self::assertArrayHasKey('title', $data['errors']);
    }

    public function testListFiltersAndSorts(): void
    {
        $this->request('POST', '/api/tasks', ['title' => 'Later', 'due_date' => $this->future('+10 days')]);
        $this->request('POST', '/api/tasks', ['title' => 'Sooner', 'due_date' => $this->future('+1 day')]);
        $this->request('POST', '/api/tasks', ['title' => 'Done one', 'status' => 'done', 'due_date' => $this->future('+2 days')]);

        $all = $this->request('GET', '/api/tasks');
        self::assertResponseIsSuccessful();
        self::assertSame(['Sooner', 'Done one', 'Later'], array_column($all, 'title'));

        $todo = $this->request('GET', '/api/tasks?status=todo');
        self::assertSame(['Sooner', 'Later'], array_column($todo, 'title'));

        $dueBefore = urlencode((new \DateTimeImmutable('+5 days'))->format(\DATE_ATOM));
        $soon = $this->request('GET', '/api/tasks?status=todo&due_before='.$dueBefore);
        self::assertSame(['Sooner'], array_column($soon, 'title'));
    }

    public function testListRejectsInvalidStatusFilter(): void
    {
        $data = $this->request('GET', '/api/tasks?status=nope');

        self::assertResponseStatusCodeSame(422);
        self::assertArrayHasKey('status', $data['errors']);
    }

    public function testUpdateStatus(): void
    {
        $task = $this->request('POST', '/api/tasks', ['title' => 'Ship it']);

        $data = $this->request('PATCH', '/api/tasks/'.$task['id'].'/status', ['status' => 'in_progress']);

        self::assertResponseIsSuccessful();
        self::assertSame('in_progress', $data['status']);
        self::assertSame($task['title'], $data['title']);
    }

    public function testUpdateStatusReturns404ForUnknownTask(): void
    {
        $data = $this->request('PATCH', '/api/tasks/999999/status', ['status' => 'done']);

        self::assertResponseStatusCodeSame(404);
        self::assertSame(['id' => ['Task not found.']], $data['errors']);
    }

    public function testUpdateStatusReturns422ForInvalidStatus(): void
    {
        $task = $this->request('POST', '/api/tasks', ['title' => 'Ship it']);

        $data = $this->request('PATCH', '/api/tasks/'.$task['id'].'/status', ['status' => 'archived']);

        self::assertResponseStatusCodeSame(422);
        self::assertArrayHasKey('status', $data['errors']);
    }

    private function future(string $modifier): string
    {
        return (new \DateTimeImmutable($modifier))->format(\DATE_ATOM);
    }

    /**
     * @param array<string, mixed>|null $body
     *
     * @return array<mixed>
     */
    private function request(string $method, string $uri, ?array $body = null): array
    {
        $this->client->request($method, $uri, server: ['CONTENT_TYPE' => 'application/json'], content: null === $body ? null : json_encode($body));

        return json_decode($this->client->getResponse()->getContent(), true);
    }
}
