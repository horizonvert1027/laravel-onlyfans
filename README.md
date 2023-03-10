<!doctype html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="description" content="">
    <meta name="author" content="">
    <link rel="icon" href="../assets/images/favicon.png">

    <title>Update Sponzy - Support Creators Content Script v4.5</title>

    <!-- Bootstrap core CSS -->
    <link href="../assets/css/bootstrap.min.css" rel="stylesheet">

    <!-- Custom styles for this template -->
    <link href="../assets/css/styles.css" rel="stylesheet">
    <link href="../assets/css/prism.css" rel="stylesheet">
    <link href="../assets/css/prism-line-highlight.css" rel="stylesheet">
    <link href="../assets/css/all.min.css" rel="stylesheet">


    <link href='https://fonts.googleapis.com/css?family=Montserrat:700' rel='stylesheet' type='text/css' />

  </head>

  <body>

    <main role="main">

      <!-- Main jumbotron for a primary marketing message or call to action -->
      <div class="jumbotron jb-bg text-center">
        <div class="container">
          <div class="position-relative">
            <h1 class="jumbotron-title">Sponzy - Support Creators Content Script v4.5</h1>
              <p class="lead">Updated:  29, October 2022</p>
          </div>
        </div>
      </div>

<div class="container">


      <div class="row">
        <div class="col-md-12">

          <h2 class="title2">Changelog</h2>

          <ul class="list-cgl">

            <li class="title-list">Fixed</li>
            <li>Verify ZIP creator rule</li>
            <li>Account verification (Email incorrect)</li>
            <li>Stories with text disappeared on close</li>
            <li>Pagination with filter on creators, live and categories</li>
            <li>Sort pagination on Members on Panel Admin</li>
            <li>Invoice error with transaction canceled</li>
            <li>Backblaze region missing</li>

            <li class="title-list">New</li>
            <li>Push notification on New messages (Only if the user has 10 minutes offline and less than 1 day)</li>
            <li>Withdrawal option with Western Union</li>
            <li>Restriction of videos to MP4 if encoding is disabled</li>
            <li>Increased story thumbnail size</li>
            <li>Username to registration with first name and ID</li>
            <li>Images and videos adapted in stories on mobile devices</li>
            <li>More visible close story button and it works</li>
          </ul><!-- /.ul list-cgl -->

          <h2 class="title2">Installation</h2>

          <ul class="list-cgl decimal affected-files">
            <div class="alert alert-sm alert-danger" role="alert">
              <i class="fas fa-exclamation-triangle"></i>
              <strong>IMPORTANT:</strong>
              If you have made changes to the script make a backup of the files, because they can be replaced.
            </div>

            <div class="alert alert-sm alert-danger" role="alert">
              <i class="fas fa-exclamation-triangle"></i>
              <strong>IMPORTANT:</strong>
              You must update from <strong><em>version 4.4</strong></em>. if you upgrade from another version will throw an error.
            </div>

            <div class="alert alert-sm alert-danger" role="alert">
              <i class="fas fa-exclamation-triangle"></i>
              <strong>IMPORTANT:</strong>
              Please follow the steps below strictly, otherwise the update will fail.
            </div>

            <li>Upload the file <strong><code>v4.5.zip</code></strong> found inside the <strong><code>Update-v4.5</code></strong> folder, into the <strong><code>public_html</code></strong> or <strong><code>www</code></strong> folder on your server, or where you have the script installed, you must make sure that it is the root directory.</li>
            <br />

             <li>Unzip the file <strong><code>v4.5.zip</code></strong> and enter at URL <strong><code>https://yoursite.com/v4.5/</code></strong> or if you have the script in subdirectory (subdomain) <strong><code>https://yoursite.com/test/v4.5/</code></strong></li>
             <div class="alert alert-sm alert-warning" role="alert">
               <i class="fas fa-exclamation-triangle"></i>
               <strong>IMPORTANT:</strong>
               You must be logged in as Admin
             </div>
             <br />

             <li>If all goes well, this message will appear, that's it! The new version has already been installed.</li>
             <img class="img-fluid mb-5" src="../assets/images/01.png">
<br />

<li>Add these new text strings if you have created a language other than Spanish or English, otherwise you should not do anything because they will be added automatically. <strong><code>resources / lang / xx / general.php</code></strong></li>
             <pre><code class="language-php">// Version 4.5
'document_id' => 'Document ID',
'new_msg_from' => 'New message from',</code></pre>

<h6 class="alert alert-sm alert-info" role="alert">
  <i class="fas fa-info-circle mr-1"></i>
  If you have multiple languages on your site you should add to each file.
</h6>
          <hr />

          <div class="text-center">
            <p class="lead">Any problem or doubt send me an email to <strong><a href="mailto:support@miguelvasquez.net">support@miguelvasquez.net</a></strong> <br/>
            Do not forget to visit <strong><a href="https://miguelvasquez.net/" target="_blank">miguelvasquez.net</a></strong></p>
          </div>
        </div><!-- /.col-md-12 -->
      </div><!-- /.row -->
    </div><!-- /.container -->

    </main><!-- /.container -->

    <footer class="footer">
      <div class="container">
        <span class="text-muted">&copy; Miguel Vasquez - Web Design and Development All Rights Reserved.
          <a href="https://www.facebook.com/MiguelVasquezWeb"><i class="fab fa-facebook"></i></a>
          <a href="https://twitter.com/MigueVasquezweb"><i class="fab fa-twitter"></i></a>
          <a href="https://instagram.com/miguelvasquezweb"><i class="fab fa-instagram"></i></a>
        </span>
      </div>
    </footer>

    <!-- Bootstrap core JavaScript
    ================================================== -->
    <!-- Placed at the end of the document so the pages load faster -->
    <script type="text/javascript" src="../assets/js/jquery-3.3.1.min.js"></script>
    <script type="text/javascript" src="../assets/js/bootstrap.min.js"></script>
    <script type="text/javascript" src="../assets/js/prism.js"></script>
    <script type="text/javascript" src="../assets/js/prism-line-highlight.js"></script>
    <script type="text/javascript" src="../assets/js/jquery.scrollTo.min.js"></script>
  </body>
</html>
