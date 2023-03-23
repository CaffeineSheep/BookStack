<div class="entity-list">
    @if(count($entities) > 0)
        @foreach($entities as $index => $entity)

            @php
                $locked = $permission !== 'view' && !userCan($permission, $entity) && ((($entity->slug !== 'community-review' && $entity->slug !== 'draft-help') && userCan('page-update', $entity)) || ($entity->slug == 'general' && !userCan('page-update', $entity))) && $entity instanceof \BookStack\Entities\Models\Book;
            @endphp

            @if (!$locked)
                @include('entities.list-item', [
                'entity' => $entity,
                'showPath' => true,
                'locked' => $locked
                ])
            
                @if($index !== count($entities) - 1)
                    <hr>
                @endif
            @endif

        @endforeach
    @else
        <p class="text-muted text-large p-xl">
            {{ trans('common.no_items') }}
        </p>
    @endif
</div>