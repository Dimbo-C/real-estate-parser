<div class="card">
    <div class="card-content">
        <span class="card-title black-text">Get file from http://casa.it/</span>
        <form action="<?php echo $_SERVER['REQUEST_SCHEME'] . '://' . $_SERVER['HTTP_HOST'] ?>/parser/generator.php"
              method="post">
            <div class="row">
                <div class="input-field col s12">
                    <input id="url" placeholder="Set Link" type="text" name="url"
                           value="<?php isset($_POST['url']) ? $_POST['url'] : '' ?>">
                    <label for="url">URL</label>
                </div>
            </div>
            <div class="card-action">
                <input type="submit" class="btn" value="GENERATE">
            </div>
        </form>
    </div>
</div>