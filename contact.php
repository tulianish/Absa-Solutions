<?php
// OPTIONS - PLEASE CONFIGURE THESE BEFORE USE!

$yourEmail = "YOUR EMAIL"; // the email address you wish to receive these mails through
$yourWebsite = "WEBSITE NAME"; // the name of your website
$thanksPage = ''; // URL to 'thanks for sending mail' page; leave empty to keep message on the same page 
$maxPoints = 4; // max points a person can hit before it refuses to submit - recommend 4
$requiredFields = "name,email,comments"; // names of the fields you'd like to be required as a minimum, separate each field with a comma


// DO NOT EDIT BELOW HERE
$error_msg = null;
$result = null;

$requiredFields = explode(",", $requiredFields);

function clean($data)
{
    $data = trim(stripslashes(strip_tags($data)));
    return $data;
}

function isBot()
{
    $bots = array("Indy", "Blaiz", "Java", "libwww-perl", "Python", "OutfoxBot", "User-Agent", "PycURL", "AlphaServer", "T8Abot", "Syntryx", "WinHttp", "WebBandit", "nicebot", "Teoma", "alexa", "froogle", "inktomi", "looksmart", "URL_Spider_SQL", "Firefly", "NationalDirectory", "Ask Jeeves", "TECNOSEEK", "InfoSeek", "WebFindBot", "girafabot", "crawler", "www.galaxy.com", "Googlebot", "Scooter", "Slurp", "appie", "FAST", "WebBug", "Spade", "ZyBorg", "rabaz");

    foreach ($bots as $bot)
        if (stripos($_SERVER['HTTP_USER_AGENT'], $bot) !== false)
            return true;

    if (empty($_SERVER['HTTP_USER_AGENT']) || $_SERVER['HTTP_USER_AGENT'] == " ")
        return true;

    return false;
}

if ($_SERVER['REQUEST_METHOD'] == "POST") {
    if (isBot() !== false)
        $error_msg .= "No bots please! UA reported as: " . $_SERVER['HTTP_USER_AGENT'];

    // lets check a few things - not enough to trigger an error on their own, but worth assigning a spam score..
    // score quickly adds up therefore allowing genuine users with 'accidental' score through but cutting out real spam :)
    $points = (int)0;

    $badwords = array("adult", "beastial", "bestial", "blowjob", "clit", "cum", "cunilingus", "cunillingus", "cunnilingus", "cunt", "ejaculate", "fag", "felatio", "fellatio", "fuck", "fuk", "fuks", "gangbang", "gangbanged", "gangbangs", "hotsex", "hardcode", "jism", "jiz", "orgasim", "orgasims", "orgasm", "orgasms", "phonesex", "phuk", "phuq", "pussies", "pussy", "spunk", "xxx", "viagra", "phentermine", "tramadol", "adipex", "advai", "alprazolam", "ambien", "ambian", "amoxicillin", "antivert", "blackjack", "backgammon", "texas", "holdem", "poker", "carisoprodol", "ciara", "ciprofloxacin", "debt", "dating", "porn", "link=", "voyeur", "content-type", "bcc:", "cc:", "document.cookie", "onclick", "onload", "javascript");

    foreach ($badwords as $word)
        if (
            strpos(strtolower($_POST['comments']), $word) !== false ||
            strpos(strtolower($_POST['name']), $word) !== false
        )
            $points += 2;

    if (strpos($_POST['comments'], "http://") !== false || strpos($_POST['comments'], "www.") !== false)
        $points += 2;
    if (isset($_POST['nojs']))
        $points += 1;
    if (preg_match("/(<.*>)/i", $_POST['comments']))
        $points += 2;
    if (strlen($_POST['name']) < 3)
        $points += 1;
    if (strlen($_POST['comments']) < 15 || strlen($_POST['comments'] > 1500))
        $points += 2;
    // end score assignments

    foreach ($requiredFields as $field) {
        trim($_POST[$field]);

        if (!isset($_POST[$field]) || empty($_POST[$field]))
            $error_msg .= "Please fill in all the required fields and submit again.\r\n";
    }

    if (!preg_match("/^[a-zA-Z-'\s]*$/", stripslashes($_POST['name'])))
        $error_msg .= "The name field must not contain special characters.\r\n";
    if (!preg_match('/^([a-z0-9])(([-a-z0-9._])*([a-z0-9]))*\@([a-z0-9])(([a-z0-9-])*([a-z0-9]))+' . '(\.([a-z0-9])([-a-z0-9_-])?([a-z0-9])+)+$/i', strtolower($_POST['email'])))
        $error_msg .= "That is not a valid e-mail address.\r\n";
    if (!empty($_POST['url']) && !preg_match('/^(http|https):\/\/(([A-Z0-9][A-Z0-9_-]*)(\.[A-Z0-9][A-Z0-9_-]*)+)(:(\d+))?\/?/i', $_POST['url']))
        $error_msg .= "Invalid website url.\r\n";

    if ($error_msg == NULL && $points <= $maxPoints) {
        $subject = "Automatic Form Email";

        $message = "You received this e-mail message through your website: \n\n";
        foreach ($_POST as $key => $val) {
            $message .= ucwords($key) . ": " . clean($val) . "\r\n";
        }
        $message .= "\r\n";
        $message .= 'IP: ' . $_SERVER['REMOTE_ADDR'] . "\r\n";
        $message .= 'Browser: ' . $_SERVER['HTTP_USER_AGENT'] . "\r\n";
        $message .= 'Points: ' . $points;

        if (strstr($_SERVER['SERVER_SOFTWARE'], "Win")) {
            $headers = "From: $yourEmail\n";
            $headers .= "Reply-To: {$_POST['email']}";
        } else {
            $headers = "From: $yourWebsite <$yourEmail>\n";
            $headers .= "Reply-To: {$_POST['email']}";
        }

        if (mail($yourEmail, $subject, $message, $headers)) {
            if (!empty($thanksPage)) {
                header("Location: $thanksPage");
                exit;
            } else {
                $result = 'Your mail was successfully sent.';
                $disable = true;
            }
        } else {
            $error_msg = 'Your mail could not be sent this time. [' . $points . ']';
        }
    } else {
        if (empty($error_msg))
            $error_msg = 'Your mail looks too much like spam, and could not be sent this time. [' . $points . ']';
    }
}
function get_data($var)
{
    if (isset($_POST[$var]))
        echo htmlspecialchars($_POST[$var]);
}

?>

<!DOCTYPE html>
<title>Elegant Press | Contact</title>
<meta charset="utf-8"/>
<link rel="stylesheet" href="styles/style.css" type="text/css"/>
<link rel="stylesheet" href="styles/prettyphoto.css" type="text/css"/>
<link rel="stylesheet" href="styles/totop.css" type="text/css"/>

<!--[if IE]>
<style>#header h1 a:hover {
    font-size: 75px;
}</style><![endif]-->
</head>
<body>
<div class="main-container">
    <header>
        <h1><a href="index.html">Elegant Press</a></h1>

        <p id="tagline"><strong>Clean Website Template</strong></p>
    </header>
</div>
<div class="main-container">
    <div id="sub-headline">
        <div class="tagline_left"><p id="tagline2">Tel: 123 333 4444 | Mail: <a href="mailto:email@website.com">email@website.com</a>
            </p></div>
        <div class="tagline_right">
            <form action="#" method="post">
                <fieldset>
                    <legend>Site Search</legend>
                    <input type="text" value="Search Our Website&hellip;"
                           onfocus="if (this.value == 'Search Our Website&hellip;') {this.value = '';}"
                           onblur="if (this.value == '') {this.value = 'Search Our Website&hellip;';}"/>
                    <input type="submit" name="go" id="go" value="Search"/>
                </fieldset>
            </form>
        </div>
        <br class="clear"/>
    </div>
</div>
<div class="main-container">
    <div id="nav-container">
        <nav>
            <ul class="nav">
                <li><a href="index.html">Homepage</a></li>
                <li><a href="typo.html">Typography</a></li>
                <li><a href="#">Layouts</a>
                    <ul>
                        <li><a href="#">Sidebar</a>
                            <ul>
                                <li><a href="right_sidebar.html">Right Sidebar</a>
                                </li>
                            </ul>
                        <li><a href="full_width.html">Full Width</a></li>
                    </ul>
                </li>
                <li><a href="portfolio.html">Portfolio</a></li>
                <li><a href="gallery.html">Gallery</a></li>
                <li class="active last"><a href="contact.php">Contact</a></li>
            </ul>
        </nav>
        <div class="clear"></div>
    </div>
</div>
<div class="main-container">
    <div class="container1">
        <div id="breadcrumb">
            <ul>
                <li class="first">You Are Here</li>
                <li>&#187;</li>
                <li><a href="index.html">Homepage</a></li>
                <li>&#187;</li>
                <li class="current"><a href="contact.html">Contact</a></li>
            </ul>
        </div>
        <br/>
        <br/>

        <div class="box">
            <div class="content">
                <h1>Headline: Contact Us</h1>
                <?php
                if ($error_msg != NULL) {
                    echo '<p class="error">ERROR: ' . nl2br($error_msg) . "</p>";
                }
                if ($result != NULL) {
                    echo '<p class="success">' . $result . "</p>";
                }
                ?>

                <form action="<?php echo basename(__FILE__); ?>" method="post">
                    <noscript>
                        <p><input type="hidden" name="nojs" id="nojs"/></p>
                    </noscript>


                    <br/>

                    <p>
                        <input type="text" name="name" id="name" value="<?php get_data("name"); ?>" size="22"/>
                        <label for="name">
                            <small>Name (required)</small>
                        </label>
                    </p>
                    <p>
                        <input type="text" name="email" id="email" value="<?php get_data("email"); ?>" size="22"/>
                        <label for="email">
                            <small>Mail (required)</small>
                        </label>
                    </p>
                    <p>
                        <textarea name="comments" id="comments" rows="10"><?php get_data("comments"); ?></textarea>
                        <label for="comments" style="display:none;">
                            <small>Comment (required)</small>
                        </label>
                    </p>
                    <p>
                        <input name="submit" type="submit" id="submit"
                               value="Submit Form"  <?php if (isset($disable) && $disable === true) echo ' disabled="disabled"'; ?> />
                        &nbsp;
                        <input name="reset" type="reset" id="reset" tabindex="5" value="Reset Form"/>
                    </p>
                </form>

            </div>

            <div class="sidebar">

                <div id="featured">
                    <ul>
                        <li>
                            <h5>Widget</h5>

                            <p>
                                <iframe width="220" height="350"
                                        src="http://maps.google.com/maps?q=701+first+ave+sunnyvale+ca&amp;ie=UTF8&amp;hl=en&amp;hq=&amp;hnear=701+1st+Ave,+Sunnyvale,+California+94089&amp;z=14&amp;ll=37.41696,-122.02531&amp;output=embed"></iframe>
                                <br/>
                                <small><a
                                        href="http://maps.google.com/maps?q=701+first+ave+sunnyvale+ca&amp;ie=UTF8&amp;hl=en&amp;hq=&amp;hnear=701+1st+Ave,+Sunnyvale,+California+94089&amp;z=14&amp;ll=37.41696,-122.02531&amp;source=embed"
                                        style="color:#0000FF;text-align:left">View Larger Map</a></small>
                            </p>
                        </li>
                    </ul>
                </div>
                <div class="subnav">
                    <h5>Follow Us!</h5>
                    <ul>
                        <li><a href="#">Facebook</a></li>
                        <li><a href="#">Twitter</a></li>
                        <li><a href="#">Linkedin</a></li>
                    </ul>
                </div>
            </div>


            <div class="clear"></div>
        </div>


    </div>
    <div class="main-container">
    </div>

    <div class="callout">
        <div class="calloutcontainer">
            <div class="container_12">
                <div class="grid">
                    <article class="footbox">
                        <h2>From The Blog</h2>
                        <ul>
                            <li><a href="#">Lorem Ipsum Dolor</a><br/>
                                Orciint erdum condimen terdum nulla mcorper elit nam curabitur...
                            </li>
                            <li><a href="#">Praesent Et Eros</a><br/>
                                Orciint erdum condimen terdum nulla mcorper elit nam curabitur...
                            </li>
                            <li><a href="#">Suspendisse In Neque</a><br/>
                                Orciint erdum condimen terdum nulla mcorper elit nam curabitur...
                            </li>
                        </ul>
                    </article>
                    <article class="footbox last">
                        <h2>We Are Social!</h2>

                        <div id="social">
                            <a href="http://twitter.com/priteshgupta" class="s3d twitter"> Twitter <span
                                    class="icons twitter"></span> </a>
                            <a href="http://www.facebook.com/priteshgupta" class="s3d facebook"> Facebook <span
                                    class="icons facebook"></span> </a>
                            <a href="http://forrst.com/people/priteshgupta" class="s3d forrst"> Forrst <span
                                    class="icons forrst"></span> </a>
                            <a href="http://www.flickr.com/photos/priteshgupta" class="s3d flickr"> Flickr <span
                                    class="icons flickr"></span> </a>
                            <a href="#" class="s3d designmoo"> Designmoo <span class="icons designmoo"></span> </a>
                        </div>
                    </article>
                    <article class="latestgallery">
                        <h2>Latest Work</h2>
                        <ul>
                            <li><a href="images/thumb.jpg" data-gal="prettyPhoto[gallery]" title="Title"><img
                                        src="images/thumb.jpg" alt="" width="150" height="95"/></a></li>
                            <li><a href="images/thumb2.jpg" data-gal="prettyPhoto[gallery]" title="Title"><img
                                        src="images/thumb2.jpg" alt="" width="150" height="95"/></a></li>
                            <li><a href="images/thumb3.jpg" data-gal="prettyPhoto[gallery]" title="Title"><img
                                        src="images/thumb3.jpg" alt="" width="150" height="95"/></a></li>
                            <li><a href="images/thumb4.jpg" data-gal="prettyPhoto[gallery]" title="Title"><img
                                        src="images/thumb4.jpg" alt="" width="150" height="95"/></a></li>
                        </ul>
                    </article>

                    <div class="clear"></div>
                </div>
                <div class="calloutoverlay"></div>
                <div class="calloutoverlaybottom"></div>
            </div>
        </div>
    </div>
    <footer>
        <p class="tagline_left">Copyright &copy; 2011 - All Rights Reserved - <a href="#">Domain Name</a></p>

        <p class="tagline_right">Design by <a href="http://www.priteshgupta.com/" title="Pritesh Gupta" target="_blank">PriteshGupta.com</a>
        </p>
        <br class="clear"/>
    </footer>

    <br/>
    <br/>
</div>
<script src="scripts/jquery.js" type="text/javascript"></script>
<script src="scripts/prettyphoto.js" type="text/javascript"></script>
<script src="scripts/jflow.js" type="text/javascript"></script>
<script src="scripts/easing.js" type="text/javascript"></script>
<script src="scripts/totop.js" type="text/javascript"></script>
<script src="scripts/superfish.js" type="text/javascript"></script>
<script src="scripts/functions.js" type="text/javascript"></script>
</body>
</html>
