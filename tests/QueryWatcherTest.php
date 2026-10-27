<?php

namespace Haminh7036\TelescopeExtras\Tests;

use Haminh7036\TelescopeExtras\Watchers\QueryWatcher;
use Illuminate\Database\Connection;
use Illuminate\Database\Events\QueryExecuted;

class QueryWatcherTest extends TestCase
{
    public function test_named_bindings_sharing_a_prefix_are_replaced_by_full_name()
    {
        $sql = $this->replaceBindings(
            'update "t" set "a" = :content1, "b" = :content10 where "type" = :type',
            ['content1' => 'a', 'content10' => 'b', 'type' => 'query']
        );

        $this->assertSame(<<<'SQL'
update "t" set "a" = 'a', "b" = 'b' where "type" = 'query'
SQL, $sql);
    }

    public function test_positional_and_null_bindings_still_work()
    {
        $sql = $this->replaceBindings('select * from "t" where "a" = ? and "b" = ? and "c" is ?', ['x', 2, null]);

        $this->assertSame(<<<'SQL'
select * from "t" where "a" = 'x' and "b" = 2 and "c" is null
SQL, $sql);
    }

    protected function replaceBindings(string $sql, array $bindings): string
    {
        // No PDO behind it, so Telescope falls back to its own string quoting.
        $event = new QueryExecuted($sql, $bindings, 1.0, new Connection(fn () => null));

        return (new QueryWatcher)->replaceBindings($event);
    }
}
