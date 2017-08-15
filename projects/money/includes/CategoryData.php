<?php
namespace wangjian\wangjianio\projects\money;

require_once __DIR__ . '/Database.php';

class CategoryData extends Database
{
    public function printData($type, $cate_1 = null, $cate_2 = null, $cate_3 = null, $cate_4 = null, $cate_5 = null)
    {
        switch ($type) {
            case '收入':
                $t_type = 'in';
                break;
            case '支出':
                $t_type = 'out';
                break;

            default:
                exit;
                break;
        }

        $table = "category_$t_type";

        $username = 'money_root';
        $this->connect($username);

        $current_url = $_SERVER['PHP_SELF'].'?'.$_SERVER['QUERY_STRING'];
        $back_url = preg_replace("/-[^-]+$/", '', $current_url);
        $back_img = '<a href="' . $back_url . '"><img class="svg category-back" src="/icons/back.svg"></a>';

        if (empty($cate_1)) {
            $cate_head = '类别';
            $sql = "SELECT {$t_type}_1 FROM $table GROUP BY {$t_type}_1";
            $stmt = $this->mysqli->prepare($sql);
        } elseif (empty($cate_2)) {
            $cate_head = $back_img . $cate_1;
            $sql = "SELECT {$t_type}_2 FROM $table WHERE {$t_type}_1 = ? GROUP BY {$t_type}_2";
            if ($stmt = $this->mysqli->prepare($sql)) {
                $stmt->bind_param("s", $cate_1);
            }
        } elseif (empty($cate_3)) {
            $cate_head = $back_img . $cate_2;
            $sql = "SELECT {$t_type}_3 FROM $table WHERE {$t_type}_2 = ? AND {$t_type}_1 = ? GROUP BY {$t_type}_3";
            if ($stmt = $this->mysqli->prepare($sql)) {
                $stmt->bind_param("ss", $cate_2, $cate_1);
            }
        } elseif (empty($cate_4)) {
            $cate_head = $back_img . $cate_3;
            $sql = "SELECT {$t_type}_4 FROM $table WHERE {$t_type}_3 = ? AND {$t_type}_2 = ? AND {$t_type}_1 = ? GROUP BY {$t_type}_4";
            if ($stmt = $this->mysqli->prepare($sql)) {
                $stmt->bind_param("sss", $cate_3, $cate_2, $cate_1);
            }
        } else {
            $cate_head = $back_img . $cate_4;
            $sql = "SELECT {$t_type}_5 FROM $table WHERE {$t_type}_4 = ? AND {$t_type}_3 = ? AND {$t_type}_2 = ? AND {$t_type}_1 = ? GROUP BY {$t_type}_5";
            if ($stmt = $this->mysqli->prepare($sql)) {
                $stmt->bind_param("ssss", $cate_4, $cate_3, $cate_2, $cate_1);
            }
        }

        $this->printH1($t_type);
        echo '<div class="row">';
        echo '<div class="col-xs-12 col-sm-6 col-md-5">';
        echo '<table class="table table-hover">';
        $this->printTableHead($cate_head);
        $this->printTableFoot();
        echo "<tbody>";
            $stmt->execute();
            $stmt->bind_result($result);
            while ($stmt->fetch()) {
                if (isset($result)) {
                    $url = "{$_SERVER['PHP_SELF']}?{$_SERVER['QUERY_STRING']}-$result";
                    if (isset($cate_4)) {
                        unset($url);
                    }
                    $id = "{$_SERVER['QUERY_STRING']}-$result";
                    $id = preg_replace("/^c=/", '', $id);
                    echo <<<TR
                    <tr id="$result">
                      <td class="cate-name" id="{$result}Col1" onclick="location.href='$url'">$result</td>
                      <td class="edit" id="{$result}Col2"><a href="javascript:void(0)" onclick="checkIsThereEditForm('$id', '$result')">编辑</a></td>
                      <td class="delete" id="{$result}Col3"><a href="delete.php?c=$id">删除</a></td>
                    </tr>
TR;
                }
            }
            $stmt->close();
            $this->mysqli->close();
        echo "</tbody>";
        echo "</table>";
        echo '</div><!-- .col -->';
        echo '</div><!-- .row -->';
    }

    public function printH1($t_type)
    {
        if ($t_type === 'out') {
            echo <<<H1
            <div class="page-header">
              <h1>
                <a href="/projects/money/category/index.php?c=支出">支出</a>
                <small><a href="/projects/money/category/index.php?c=收入">收入</a></small>
              </h1>
            </div>
H1;
        } elseif ($t_type === 'in') {
            echo <<<H1
            <div class="page-header">
              <h1>
                <small><a href="/projects/money/category/index.php?c=支出">支出</a></small>
                <a href="/projects/money/category/index.php?c=收入">收入</a>
              </h1>
            </div>
H1;
        } else {
            echo 'error';
        }
    }

    public function printTable()
    {
        echo "<table>";
        $this->printTableHead($cate_head);
        $this->printTableFoot();
        echo "</table>";
    }

    public function printTableHead($cate_head)
    {
        echo <<<THEAD
        <thead>
          <tr>
            <th class="cate-head" colspan="3">$cate_head</th>
          </tr>
        </thead>
THEAD;
    }

    public function printTableFoot()
    {
        echo <<<TFOOT
        <tfoot>
          <tr>
            <td class="blank" id="blank"></td>
            <td class="add" id="add" colspan="2">
              <a href="javascript:void(0)" onclick="showAddForm('{$_SERVER['QUERY_STRING']}')">新增</a>
            </td>
          </tr>
        </tfoot>
TFOOT;
    }

    public function printTableBody()
    {

    }
}

$category_data = new CategoryData;
