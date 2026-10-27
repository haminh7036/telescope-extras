<?php

namespace Haminh7036\TelescopeExtras\Watchers;

use Laravel\Telescope\Watchers\QueryWatcher as BaseQueryWatcher;

class QueryWatcher extends BaseQueryWatcher
{
    /**
     * Replace the placeholders with the actual bindings.
     *
     * Same as Telescope's implementation, except named placeholders must match
     * the full name, so ":content1" no longer matches inside ":content10".
     *
     * @see https://github.com/laravel/telescope/pull/1765
     *
     * @param  \Illuminate\Database\Events\QueryExecuted  $event
     * @return string
     */
    public function replaceBindings($event)
    {
        $sql = $event->sql;

        foreach ($this->formatBindings($event) as $key => $binding) {
            $regex = is_numeric($key)
                ? "/\?(?=(?:[^'\\\']*'[^'\\\']*')*[^'\\\']*$)/"
                : "/:{$key}(?![a-zA-Z0-9_])(?=(?:[^'\\\']*'[^'\\\']*')*[^'\\\']*$)/";

            if ($binding === null) {
                $binding = 'null';
            } elseif (! is_int($binding) && ! is_float($binding)) {
                $binding = $this->quoteStringBinding($event, $binding);
            }

            $sql = preg_replace(
                $regex,
                $binding,
                $sql,
                is_numeric($key) ? 1 : -1
            );
        }

        return $sql;
    }
}
