<h1>dummy login</h1>
<form action="{{url('/login')}}" method="POST">
    @csrf
    <input type="text" name="username" value="admin@pac.com"> <br />
    <input type="text" name="password" value="1qaz2wsx"> <br />
    <input type="text" name="return_url" value="http://127.0.0.1:8010/call-back"> <br />
    <input type="submit">
</form>