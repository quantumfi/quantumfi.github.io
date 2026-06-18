<br/>
<div class="row">
	<img src="img/logo.png" class="center-block text-center" title="FERMION | Fermi Crypto Currency" alt="FERMION | Fermi Crypto Currency">
</div>
<div class="row">
</br/><h1 class="text-center">FERMION</h1><br/></br/>
<div class="thumbnail col-lg-offset-2 col-lg-8">
<form class="form-horizontal" action="register" method="post">
  <fieldset>
    <legend>Sign Up</legend>
    <div class="form-group">
      <label class="col-lg-3 control-label">Username</label>
      <div class="col-lg-9">
        <input type="text" class="form-control" name="username" placeholder="Username" required>
      </div>
    </div>
    <div class="form-group">
      <label class="col-lg-3 control-label">Email</label>
      <div class="col-lg-9">
        <input type="email" class="form-control" name="email" placeholder="Email" required oninput="setInputVal(value, 'pref_email')">
      </div>
    </div>
    <div class="form-group">
      <label class="col-lg-3 control-label">Password</label>
      <div class="col-lg-9">
        <input type="password" class="form-control" name="password" placeholder="Password" required>
      </div>
    </div>
	<div class="form-group">
      <div class="col-lg-9 col-lg-offset-3">
        <button type="reset" class="btn btn-default">Cancel</button>
        <button type="submit" class="btn btn-primary">Submit</button>
        <br/><br/><a href="login" class="text-info"><span class="glyphicon glyphicon-log-in"></span> Sign In </a>&nbsp;
		<a href="consentForm" class="text-info"><span class="glyphicon glyphicon-th-list"></span> Consent Form </a>&nbsp;
		<br/><br/><a href="/" class="text-warning">Back to FERMION main page</a>
      </div>
    </div>
  </fieldset>
</form>
</div>
</div>
<script>
function setInputVal(val, id){
	document.getElementById(id).value = val;
}
</script>