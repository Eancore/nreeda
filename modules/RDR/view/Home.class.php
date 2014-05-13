<?php
/**
 * This file is part of Choqled PHP Framework and/or part of a BFLDEV Software Product.
 * This file is licensed under "GNU General Public License" Version 3 (GPL v3).
 * If you find a bug or you want to contribute some code snippets, let me know at http://bfldev.com/nreeda
 * Suggestions and ideas are also always helpful.

 * @author Roland Eigelsreiter (BrainFooLong)
 * @product nReeda - Web-based Open Source RSS/XML/Atom Feed Reader
 * @link http://bfldev.com/nreeda
**/

if(!defined("CHOQ")) die();
/**
* The home
*/
class RDR_Home extends CHOQ_View{

    /**
    * Load the View
    */
    public function onLoad(){
        if(req()->isAjax()){
            if(needRole(RDR_User::ROLE_ADMIN)){
                if(post("clearlog")){
                    $logs = array();
                    db()->query("TRUNCATE TABLE ".db()->quote("RDR_Event"));
                    db()->query("DELETE FROM _choqled_metadata WHERE ".db()->quote("type")." = 'RDR_Event'");
                    while($logs = db()->getByCondition("RDR_Event", NULL, NULL, NULL, 1000)){
                        db()->deleteMultiple($logs);
                    }
                }
            }
            return;
        }
        view("RDR_BasicFrame", array("view" => $this));
    }

    /**
    * Get content
    */
    public function getContent(){
        headline(t("dashboard"));

        $settings = array(
            "note.opml.import" => array("url" => l("RDR_Organize")),
            "note.addfeed" => array("url" => ""),
            "note.bug" => array("url" => "https://bfldev.com/nreeda"),
            "note.opml.export" => array("url" => l("RDR_Organize")),
            "note.search" => array("url" => ""),
            "note.settings" => array("url" => "")
        );

        echo sprintf(t("hello"), '<b>'.user()->username.'</b>')."<br/><br/>";
        foreach($settings as $key => $data){
            if(user()->setting($key)) continue;
            echo '<div class="note" data-id="'.$key.'" data-url="'.$data["url"].'">'.t($key).'</div>';
        }?>

        <script type="text/javascript">
        $("#content .note").on("click", function(){
            var url = $(this).attr("data-url");
            var e = $(this);
            $.post(Global.vars.ajaxUrl, {action : "changesetting", "key" : $(this).attr("data-id"), "value" : 1}, function(data){
                e.remove();
                if(url){
                    if(url.match(/^http/)){
                        window.open(url);
                    }else{
                        window.location.href = url;
                    }
                }
            })
        });
        </script>

        <div class="spacer"></div>
        <?php
        if(needRole(RDR_User::ROLE_ADMIN)){
            headline(t("dashboard.eventlog"));
            $logs = db()->getByCondition("RDR_Event", NULL, NULL, "-id", 50);
            ?>
            <div id="eventlog">
                <?php if($logs){?><input type="button" class="btn" value="<?php echo t("dashboard.clearlog")?>"/><?php }?>
                <div class="spacer"></div>
                <?php foreach($logs as $log){?>
                    <div class="event">
                        <time datetime="<?php echo $log->createTime->getUnixtime()?>" class="inline-btn"></time>
                        <?php echo $log->getText()?>
                    </div>
                <?php }?>
            </div>
            <script type="text/javascript">
            $("#eventlog input.btn").on("click", function(){
                $.post(window.location.href, {clearlog : 1});
                $("#eventlog").remove();
            });
            </script>
            <?php
        }
    }
}