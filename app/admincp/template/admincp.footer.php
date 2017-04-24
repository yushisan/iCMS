<?php /**
 * @package iCMS
 * @copyright 2007-2017, iDreamSoft
 * @license http://www.idreamsoft.com iDreamSoft
 * @author coolmoo <idreamsoft@qq.com>
 */
defined('iPHP') OR exit('What are you doing?');
?>
  <div class="clearfloat"></div>
  <div class="iCMS-container footer-debug">
    <span class="label label-success">
      使用内存:<?php echo iFS::sizeUnit(memory_get_usage());?> 执行时间:<?php echo iPHP::timer_stop();?> s
    </span>
  </div>
</div>
<a id="scrollUp" href="#top"></a>
<div class="iCMS-batch">
  <div id="weightBatch">
    <div class="input-prepend"><span class="add-on">权重</span>
      <input type="text" class="span2" name="mweight"/>
    </div>
  </div>
  <div id="keywordBatch">
    <div class="input-prepend input-append"><span class="add-on">关键字</span>
      <input type="text" class="span2" name="mkeyword"/>
    </div>
    <div class="clearfloat mb10"></div>
    <div class="input-prepend input-append"><span class="add-on">追加
      <input type="radio" name="pattern" value="addto"/>
      </span><span class="add-on">替换
      <input type="radio" name="pattern" value="replace" checked/>
      </span></div>
  </div>
  <div id="tagBatch">
    <div class="input-prepend"><span class="add-on">标签</span>
      <input type="text" class="span2" name="mtag"/>
    </div>
    <div class="clearfloat mb10"></div>
    <div class="input-prepend input-append"><span class="add-on">追加
      <input type="radio" name="pattern" value="addto"/>
      </span><span class="add-on">替换
      <input type="radio" name="pattern" value="replace" checked/>
      </span></div>
  </div>
</div>
</body></html>
