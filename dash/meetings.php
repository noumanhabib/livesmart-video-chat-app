<?php
include_once 'header.php';
?>
<?php if ($_SESSION["tenant"] == 'lsv_mastertenant') { ?>
    <h1 class="h3 mb-2 text-gray-800" data-localize="active_meetings"></h1>
    <div class="card shadow mb-4">
        <div class="card-header py-3">
        </div>
        <div class="card-body">
            <div class="table-responsive">

                <table class="table table-bordered" id="agents_table" width="100%" cellspacing="0">
                    <thead>
                        <tr>
                            <th width="15%" class="text-center" data-localize="room"></th>
                            <th width="15%" class="text-center" data-localize="constraints"></th>
                            <th class="text-center" data-localize="participants"></th>
                            <th width="30%" class="text-center" data-localize="action"></th>
                        </tr>
                    </thead>
                    <tbody id="meetings">

                    </tbody>

                </table>
            </div>
        </div>

    </div>


<?php } ?>
<?php
include_once 'footer.php';
?>
