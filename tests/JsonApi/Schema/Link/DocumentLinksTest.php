<?php
declare(strict_types=1);

namespace WoohooLabs\Yin\Tests\JsonApi\Schema\Link;

use PHPUnit\Framework\TestCase;
use WoohooLabs\Yin\JsonApi\Schema\Link\DocumentLinks;
use WoohooLabs\Yin\JsonApi\Schema\Link\Link;
use WoohooLabs\Yin\JsonApi\Schema\Link\ProfileLinkObject;
use WoohooLabs\Yin\Tests\JsonApi\Double\StubPaginationLinkProvider;

class DocumentLinksTest extends TestCase
{
    /**
     * @test
     */
    public function createWithoutBaseUri()
    {
        $links = DocumentLinks::createWithoutBaseUri([]);

        $this->assertEquals("", $links->getBaseUri());
    }

    /**
     * @test
     */
    public function createWithBaseUri()
    {
        $links = DocumentLinks::createWithBaseUri("https://example.com", []);

        $this->assertEquals("https://example.com", $links->getBaseUri());
    }

    /**
     * @test
     */
    public function setBaseUri()
    {
        $links = $this->createLinks();

        $links->setBaseUri("https://example.com");

        $this->assertEquals("https://example.com", $links->getBaseUri());
    }

    /**
     * @test
     */
    public function transform()
    {
        $links = $this->createLinks(
            "",
            [
                "self" => new Link("https://example.com/articles/1/relationships/author"),
                "related" => new Link("https://example.com/articles/1/author"),
            ]
        );

        $transformedLinks = $links->transform();

        $this->assertArrayHasKey("self", $transformedLinks);
        $this->assertArrayHasKey("related", $transformedLinks);
    }

    /**
     * @test
     */
    public function getSelfWhenEmpty()
    {
        $links = $this->createLinks();

        $this->assertNull($links->getSelf());
    }

    /**
     * @test
     */
    public function getSelfWhenNotEmpty()
    {
        $self = new Link("https://example.com/api/users");

        $links = $this->createLinks()->setSelf($self);

        $this->assertEquals($self, $links->getSelf());
    }

    /**
     * @test
     */
    public function getRelatedWhenNotEmpty()
    {
        $related = new Link("https://example.com/api/users");

        $links = $this->createLinks()->setRelated($related);

        $this->assertEquals($related, $links->getRelated());
    }

    /**
     * @test
     */
    public function getFirstWhenEmpty()
    {
        $linksObject = $this->createLinks();

        $this->assertNull($linksObject->getFirst());
    }

    /**
     * @test
     */
    public function getFirstWhenNotEmpty()
    {
        $first = new Link("https://example.com/api/users?page[number]=1");

        $links = $this->createLinks()->setFirst($first);

        $this->assertEquals($first, $links->getFirst());
    }

    /**
     * @test
     */
    public function getLastWhenNotEmpty()
    {
        $last = new Link("https://example.com/api/users?page[number]=10");

        $links = $this->createLinks()->setLast($last);

        $this->assertEquals($last, $links->getLast());
    }

    /**
     * @test
     */
    public function getPrevWhenNotEmpty()
    {
        $prev = new Link("https://example.com/api/users?page[number]=4");

        $links = $this->createLinks()->setPrev($prev);

        $this->assertEquals($prev, $links->getPrev());
    }

    /**
     * @test
     */
    public function getNextWhenNotEmpty()
    {
        $next = new Link("https://example.com/api/users?page[number]=6");

        $links = $this->createLinks()->setNext($next);

        $this->assertEquals($next, $links->getNext());
    }

    /**
     * @test
     */
    public function setPagination()
    {
        $pagination = new StubPaginationLinkProvider();

        $links = $this->createLinks()->setPagination("https://example.com/api/users/", $pagination);

        $this->assertEquals(new Link("https://example.com/api/users/self"), $links->getSelf());
        $this->assertEquals(new Link("https://example.com/api/users/first"), $links->getFirst());
        $this->assertEquals(new Link("https://example.com/api/users/last"), $links->getLast());
        $this->assertEquals(new Link("https://example.com/api/users/prev"), $links->getPrev());
        $this->assertEquals(new Link("https://example.com/api/users/next"), $links->getNext());
    }

    /**
     * @test
     */
    public function getLink()
    {
        $self = new Link("https://example.com/api/users");

        $links = $this->createLinks()->setLink("self", $self);

        $this->assertEquals($self, $links->getLink("self"));
    }

    /**
     * @test
     */
    public function getMultipleLinks()
    {
        $self = new Link("https://example.com/api/users/1");
        $related = new Link("https://example.com/api/people/1");
        $links = ["self" => $self, "related" => $related];

        $links = $this->createLinks()->setLinks($links);

        $this->assertEquals($self, $links->getLink("self"));
        $this->assertEquals($related, $links->getLink("related"));
    }

    /**
     * @test
     */
    public function getProfiles()
    {
        $profile1 = new ProfileLinkObject("href1");
        $profile2 = new ProfileLinkObject("href2");

        $links = $this->createLinks("", [], [$profile1, $profile2]);

        $this->assertCount(2, $links->getProfiles());
        $this->assertEquals($profile1, $links->getProfiles()[0]);
        $this->assertEquals($profile2, $links->getProfiles()[1]);
    }

    /**
     * @test
     */
    public function addProfilesWithSameHref()
    {
        $profile = new ProfileLinkObject("");

        $links = $this->createLinks("", [])
            ->addProfile($profile)
            ->addProfile($profile);

        $this->assertCount(1, $links->getProfiles());
    }

    /**
     * @param Link[] $links
     * @param ProfileLinkObject[] $profiles
     */
    private function createLinks(string $baseUri = "", array $links = [], array $profiles = []): DocumentLinks
    {
        return new DocumentLinks($baseUri, $links, $profiles);
    }
}
