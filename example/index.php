<?php
    session_start();
    require_once('../src/defines.php');
    require_once('../src/InstagramBasicDisplay.php');
    
    use Zolisz\InstagramBasicDisplay\InstagramBasicDisplay as InstagramBasicDisplay;

    if(!isset($_SESSION['userAccessToken'])){
        $params = array(
            'get_code' => isset($_GET['code']) ? $_GET['code'] : ''
        );
        @$ig = new InstagramBasicDisplay();
        @$ig->firstSetup($params);
    }else{
        @$ig = new InstagramBasicDisplay();
        $ig->setup(array('access_token' => $_SESSION['userAccessToken'], 'user_id' => $_SESSION['igUserId']));
    } 

?>

<h1>INSTAGRAM BASIC DISPLAY API</h1>

<hr />
<?php if($ig->hasUserAccessToken) : ?>
    <h4>IG INFO</h4>

    <?php $user = $ig->getUser(); ?>
    <pre>
        <?php print_r($user)?>
    </pre>

    <h1>Username: <?php echo $user['username']; ?></h1>

    <h2>Ig ID: <?php echo $user['id']; ?></h2>

    <h3>Media Count: <?php echo $user['media_count']; ?></h3>

    <h4>Account type: <?php echo $user['account_type']; ?></h4> 

    <?php $usersMedia = $ig->getUserMedia(); ?>

    <pre><textarea style="width:100%;height:400px;">
    <?php print_r($usersMedia)?>
    </textarea></pre>

    <h3>Users Media Page 1 (<?php echo count($usersMedia['data']); ?>)</h3>
    <h4>Posts</h4>
    <ul style="list-style: none; margin:0px;padding:0px">
        <?php foreach($usersMedia['data'] as $posts) : ?>    
            <li style="margin-bottom: 20px; border: 3px solid #333">
                <div>
                <?php if('IMAGE' == $posts['media_type'] || 'CAROUSEL_ALBUM' == $posts['media_type']) : ?>
                    <img style="height:320px" src="<?php echo $posts['media_url']; ?>" />
                <?php else : ?>
                    <video height="240" width="320" controls>
                        <source src="<?php echo $posts['media_url']; ?>">    
                    </video>    
                <?php endif; ?>
                </div>
                <div>
                    ID: <?php echo $posts['id']?>
                </div><br>
                <div>
                    Caption: <?php echo $posts['caption']?>
                </div>  
                <div><br>
                    Media Type: <?php echo $posts['media_type']?>
                </div><br>  
                <div style="word-break: break-all;">
                    Media Url: <?php echo $posts['media_url']?>
                </div>  
            </li>
        <?php endforeach; ?>
    </ul>    

    <?php 
    if(isset($usersMedia['paging']['next'])) :
        $usersMediaNext = $ig->getPaging($usersMedia['paging']['next']); 
        echo "<h3>Users Media Next Page (" . count($usersMediaNext['data']) . ")</h3>";
    ?>        
    <h4>Posts</h4>
    <ul style="list-style: none; margin:0px;padding:0px">
        <?php foreach($usersMediaNext['data'] as $posts) : ?>    
            <li style="margin-bottom: 20px; border: 3px solid #333">
                <div>
                    <?php if('IMAGE' == $posts['media_type'] || 'CAROUSEL_ALBUM' == $posts['media_type']) : ?>
                        <img style="height:320px" src="<?php echo $posts['media_url']; ?>" />
                    <?php else : ?>
                        <video height="240" width="320" controls>
                            <source src="<?php echo $posts['media_url']; ?>">    
                        </video>    
                    <?php endif; ?>
                </div>
                <div><br>
                    ID: <?php echo $posts['id']?>
                </div><br>
                <div>
                    Caption: <?php echo $posts['caption']?>
                </div><br>
                <div>
                    Media Type: <?php echo $posts['media_type']?>
                </div><br>
                <div style="word-break: break-all;">
                    Media Url: <?php echo $posts['media_url']?>
                </div>  
            </li>
        <?php endforeach; ?>
    </ul>    
<?php endif; ?>
<?php else : ?>
    <a href="<?php echo $ig->authorizationUrl ?>">
        Authorize with Instagram
    </a>
<?php endif; ?>