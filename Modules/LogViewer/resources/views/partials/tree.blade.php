<ul class="list-unstyled mb-0">
    @foreach ($nodes as $node)
        @if ($node['type'] === 'directory')
            <li>
                <details class="log-tree-group" open>
                    <summary>
                        <x-admin.icon name="folder" class="icon icon-sm" />
                        <span>{{ $node['name'] }}</span>
                    </summary>
                    <div class="log-tree-children">
                        @include('logviewer::partials.tree', ['nodes' => $node['children'], 'selectedPath' => $selectedPath, 'lineCount' => $lineCount])
                    </div>
                </details>
            </li>
        @else
            <li>
                <a class="log-tree-file {{ $selectedPath === $node['path'] ? 'active' : '' }}" href="{{ route('admin.logviewer.index', ['file' => $node['path'], 'lines' => $lineCount]) }}" @if ($selectedPath === $node['path']) aria-current="page" @endif>
                    <x-admin.icon name="file-text" class="icon icon-sm flex-shrink-0" />
                    <span>{{ $node['name'] }}</span>
                </a>
            </li>
        @endif
    @endforeach
</ul>
