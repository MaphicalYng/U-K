<?php
/**
 * Created by PhpStorm.
 * User: Shuolin Yang
 * Date: 17/5/1
 * Time: 上午 10:37
 */?>

<?php echo validation_errors();?>

<?php echo form_open('app/log_in');?>

    <h5>用户名（不支持空格）</h5>
    <input type="text" name="id" value="">

    <h5>密码（不支持空格）</h5>
    <input type="text" name="password" value="">

    <br/><br/>

    <input type="submit" value="登录">

</form>

<?php echo anchor('app', '返回主页');?>

<br/>
