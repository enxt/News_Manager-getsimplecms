<?php if (!defined('IN_GS')) {die('you cannot load this page directly.');}

/**
 * News Manager edit comments template
 */

global $NMPAGEURL;

# image input field (since 2.5)
global $NMSETTING;

?>
<a name="comments"></a>
<h3>Comments</h3>
<table id="comments" class="highlight">
    <tr>
        <th><?php i18n('news_manager/COMMENT_NAME'); ?></th>
        <th>COMMENT</th>
        <th style="text-align: right;"><?php i18n('news_manager/DATE'); ?></th>
        <th>READED</th>
        <th>APPROVED</th>
        <th>DELETE</th>
    </tr>
    <?php
    $comments = nm_get_comments($slug);
    foreach($comments as $key=>$comment) {
        //if($key != 'dataitem') {
        ?>
            <tr>
                <td><?php echo $comment->username; ?></td>
                <td id="<?php echo 'cm_' . $comment->id; ?>">
                    <!--span onclick="js: showCompleteComment('<?php echo "cm_" . $comment->id; ?>');" style="font-style: italic;"><?php echo nm_make_excerpt($comment->message, 30, '[...]'); ?></span-->
                    <span style="font-style: italic;"><?php echo nm_make_excerpt($comment->message, 30, '[...]'); ?></span>
                    <div id="editarea" style="display: none;">
                        <textarea cols="25" rows="7"><?php echo $comment->message; ?></textarea>
                        <input type="button" value="Save comment" >
                    </div>
                </td>
                <td><?php echo shtDate($comment->date); ?></td>
                <!-- td><a href="load.php?id=news_manager&amp;slug=<?php echo $slug; ?>&amp;tgglread=<?php echo $comment->id; ?>" style="text-decoration: none; color: white; text-shadow: none;" class="<?php echo ($comment->readed == 'false')?'unreaded':'readed'; ?>"><?php echo ($comment->readed == 'false')?'NO':'YES'; ?></a></td-->
                <td><a id="<?php echo 'cmrd_' . $comment->id; ?>" href="javascript: void(0);" onclick="toggleReadComment('<?php echo $comment->id; ?>');" style="text-decoration: none; color: white; text-shadow: none;" class="<?php echo ($comment->readed == 'false')?'unreaded':'readed'; ?>"><?php echo ($comment->readed == 'false')?'NO':'YES'; ?></a></td>
                <!--td><a href="load.php?id=news_manager&amp;slug=<?php echo $slug; ?>&amp;tgglappr=<?php echo $comment->id; ?>" style="text-decoration: none; color: white; text-shadow: none;" class="<?php echo ($comment->approved == 'false')?'unapproved':'approved'; ?>"><?php echo ($comment->approved == 'false')?'NO':'YES'; ?></a></td-->
                <td><a id="<?php echo 'cmap_' . $comment->id; ?>" href="javascript: void(0);" onclick="toggleApprComment('<?php echo $comment->id; ?>');" style="text-decoration: none; color: white; text-shadow: none;" class="<?php echo ($comment->approved == 'false')?'unapproved':'approved'; ?>"><?php echo ($comment->approved == 'false')?'NO':'YES'; ?></a></td>
                <td class="delete">
                    <a href="load.php?id=news_manager&amp;slug=<?php echo $slug; ?>&amp;cmdelete=<?php echo $comment->id; ?>" class="nm_delconfirm" title="<?php i18n('news_manager/DELETE_COMMENT'); ?>: <?php echo $comment->item->username; ?>?">
                        &times;
                    </a>
                </td>
            </tr>
        <?php // }
    }
    ?>
</table>


<script type="text/javascript">
    function toggleReadComment(cm) {
        $.ajax({
            type: "POST",
            url: "load.php?id=news_manager&slug=<?php echo $slug; ?>&tgglread="+cm,
            success: function(data, status, xhr) {
                if(data == "Success") {
                    $("a#cmrd_"+cm).toggleClass("unreaded readed");
                    var oldt = $("a#cmrd_"+cm).text();
                    var newt = (oldt == "YES")?"NO":"YES";
                    $("a#cmrd_"+cm).text(newt);
                }
            }
        });
    }

    function toggleApprComment(cm) {
        $.ajax({
            type: "POST",
            url: "load.php?id=news_manager&slug=<?php echo $slug; ?>&tgglappr="+cm,
            success: function(data, status, xhr) {
                if(data == "Success") {
                    $("a#cmap_"+cm).toggleClass("unapproved approved");
                    var oldt = $("a#cmap_"+cm).text();
                    var newt = (oldt == "YES")?"NO":"YES";
                    $("a#cmap_"+cm).text(newt);
                }
            }
        });
    }

    function showCompleteComment(cm) {
        $("td#"+ cm + " span").hide(500);
        $("td#"+ cm + " div#editarea").show(500);
    }

    function hideCompleteComment(cm) {
        $("td#"+ cm + " span").show(500);
        $("td#"+ cm + " div#editarea").hide(500);
    }

    $(document).mouseup(function (e) {
        console.log($(e.target).parent.id);
        var patt = /^cm_.*/;

        if(e.target.tagName == 'SPAN' && patt.test($(e.target).parent().attr("id"))) {
            $("table#comments td span").each(function(idx, elm) {
                if($(e.target).parent().attr("id") != $(elm).parent().attr("id")) {
                    hideCompleteComment($(elm).parent().attr("id"));
                }
            });

            showCompleteComment($(e.target).parent().attr("id"));
        }
        else {
            $("table#comments td span").each(function(idx, elm) {
                if( ($(e.target).parent().attr("id") != $(elm).parent().attr("id")) && ($(e.target).parent().parent().attr("id") != $(elm).parent().attr("id")) ) {
                    console.log($(e.target).parent().attr("id") + " == " + $(elm).parent().attr("id"));
                    hideCompleteComment($(elm).parent().attr("id"));
                }
            });
        }

        /*if(e.target.tagName == 'TD' && patt.test(e.target.id)) {
            //console.log(e.target.id);
            var container = $(e.target.tagName+"#"+ e.target.id).parent();
            console.log(container);
        }*/
        //console.log(e.target.tagName);
        /*var container = $("YOUR CONTAINER SELECTOR");

        if (!container.is(e.target) // if the target of the click isn't the container...
            && container.has(e.target).length === 0) // ... nor a descendant of the container
        {
            container.hide();
        }*/
    });
</script>