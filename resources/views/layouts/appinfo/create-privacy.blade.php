@if(isset($appInfo))
    <button class="btn btn-primary font-weight-bold text-white shadow" type="submit" onclick="return confirm('Are you sure?')" formaction="{{ route('create_privacy', ['id' => $appInfo->id ]) }}" formmethod="post">
        <i class="fa fa-trash"></i>
        Create Privacy
    </button>
@endif
