
<?php


//feature this principals dashboard groups they have access too.
//check permissions for what they can do on the dashboard.


//config dynamic dashboard endpoint...

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <title>Processing Jobs</title>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width,initial-scale=1,shrink-to-fit=no">
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.4.1/css/bootstrap.min.css" integrity="sha384-Vkoo8x4CGsO3+Hhxv8T/Q5PaXtkKtu6ug5TOeNV6gBiFeWPGFN9MuhOf23Q9Ifjh" crossorigin="anonymous">
    <link href="https://cdn.jsdelivr.net/gh/gitbrent/bootstrap4-toggle@3.6.1/css/bootstrap4-toggle.min.css" rel="stylesheet">

    <script src="https://code.jquery.com/jquery-3.4.1.min.js" integrity="sha256-CSXorXvZcTkaix6Yvo6HppcZGetbYMGWSFlBw8HfCJo=" crossorigin="anonymous"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.16.1/umd/popper.js" integrity="sha256-/MqPdltDqe7iSoqjNkMb7+w1uk5FJdOpIS7YErWktBQ=" crossorigin="anonymous"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.2.1/js/bootstrap.min.js" integrity="sha384-B0UglyR+jN6CkvvICOB2joaf5I4l3gm9GU6Hc1og6Ls7i6U/mkkaduKaBhlAXv9k" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/gh/gitbrent/bootstrap4-toggle@3.6.1/js/bootstrap4-toggle.min.js"></script>
    <style>
        .item-clickable:hover{
            cursor:pointer;
        }
        .job-toggle{
            height: 30px;
            width: 30px;
            margin: -35px 0;
            background:white;
            border: 0;
            padding: 0;
            border-radius:50px;
        }
        .job-toggle svg {
            width: 24px;
        }

        .noselect {
            -webkit-touch-callout: none; /* iOS Safari */
            -webkit-user-select: none; /* Safari */
            -khtml-user-select: none; /* Konqueror HTML */
            -moz-user-select: none; /* Old versions of Firefox */
            -ms-user-select: none; /* Internet Explorer/Edge */
            user-select: none; /* Non-prefixed version, currently
                                  supported by Chrome, Opera and Firefox */
        }

        .history-output {
            font-family: monospace;
            position: relative;
            max-height:calc(100vh - 240px);
            overflow:auto;
            height:100%;
            padding:20px;
            font-size: 14px;
            white-space: pre;
            box-shadow: inset 1px 1px 6px 1px rgba(128,128,128,0.5);
        }

        tr {
            width: 100%;
            display: inline-table;
            table-layout: fixed;
        }

        table{

            height: calc(100vh - 240px);
            display: -moz-groupbox;
        }
        tbody{
            overflow-y: scroll;
            height: calc(100vh - 272px);
            width: 100%;
            position: absolute;
        }

        .modal{
            padding:0 !important;
        }

        @media (min-width: 576px){
            .modal-dialog {
                max-width: 98vw;
            }
        }

        @media (min-width: 992px){
            .modal-lg, .modal-xl {
                max-width: 98vw;
            }
        }

        @media (min-width: 1200px){
            .modal-xl {
                max-width: 1200px;
            }
        }

    </style>
    <link rel="shortcut icon" href="favicon.ico"></head>

<body>
<div class="container">
    <div class="pt-3 pb-1 row">
        <div class="col-md-8">
            <h2 class="m-0" style="color:orange"><img src="/dash/kwelanga_logo.jpg" class="img-responsive mr-3" style="max-width: 200px;">
                OMNI Dashboard
            </h2>
        </div>
        <div class="col-md-4"></h2>
        </div>
    </div>
    <div id="jobs">
        <div class="col-12 border rounded-sm bg-light mb-2 p-5 test" >
            <h1 class="display-4">loading...</h1>
        </div>
    </div>
</div>

<div class="modal fade" id="jobHistory" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-scrollable" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="exampleModalLabel">{{title}}</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body p-0" style="min-height:360px;">
                {{body}}
            </div>
        </div>
    </div>
</div>

</body>
</html>

<script>

    <?php require "../config.php";
    //MOVE THIS TO THE BACKEND, don't expose username/passwords
    echo "let apiUrl = '".DASHBOARD_URL . "';";
    echo "let basicAuth = '".base64_encode(USERNAME . ":" . PASSWORD) . "';";
    ?>

    const scheduleRequest = async(method, action, data) => {

        let req = {
            cache: "no-store",
            method: method,
            headers: {
                'Content-Type' : 'application/json',
                'Authorization': 'Basic ' + basicAuth
            }
        };

        if(typeof data === "object" && Object.keys(data).length){
            req.body = JSON.stringify(data)
        }

        const response = await fetch(apiUrl + action, req);

        try {
            const json = await response.json();
            return json;
        } catch (e){
            return response
        }

    }


    async function loadJobs(){

        let data = await scheduleRequest("GET", "/dash/api.php?collections=7c072e54-e724-4c61-9fea-83fef8ddd42e;5ca0e18f-330f-4249-a761-a4cf980fb8b8", {});
        let html = '<div class="row" >';

        console.time("loadjobs");
        console.debug("loaded");

        collectionsKeys = Object.keys(data);
        for (const key of collectionsKeys) {

            col = data[key].collection
            jobs = data[key].jobs;

            if(jobs.length == 0 || col.name == "internal"){
                continue;
            }

            html += `
			<div class="col-12 border rounded-sm bg-light mb-2 pb-2" >
					<div class="mt-3">
						<h5 class="my-1 mb-3">
							<div class="float-right collection-toggle" data-colid="${col.id}">
								<input data-size="sm" type="checkbox" `+ (col.state === "enabled" ? "checked" : "" )+` data-toggle="toggle" data-onstyle="success" data-offstyle="danger">
							</div>
							${col.name}
						</h5>
						<div style="clear:both;"></div>
					</div><div class="row">`;

            for (const job of  jobs) {

                //console.log(job.id);
                let statusText = job.status.status;
                let state = "dark";
                if(statusText == "PASSING"){
                    state = "success";
                } else if(statusText == "RUNNING"){
                    state = "primary";
                } else if(statusText == "ERRORS"){
                    state = "danger";
                }

                html += `<div class="col-md-6 col-sm-12"><div class="border rounded-sm job-item py-1 pl-3 pr-2 w-auto block bg-white `+(col.state === "enabled" ? "" : "bg-light" )+`" data-jobid="${job.id}">
					<div class=" d-flex justify-content-start align-items-center ">
						<div class="display-history noselect item-clickable">
							<span class="badge badge-${state} badge-pill mr-2" >${statusText}</span>
							<strong class="job-title ` + (col.state === "enabled" ? "" : "text-muted" ) + (state === "danger" ? " text-danger" : "" ) + `">${job.name}</strong>
						</div>
						<div class="ml-auto">
							<div class="dropdown" style="margin-top:-5px;position:relative;">
							  <button class="btn btn-light job-toggle" type="button">
								<svg enable-background="new 0 0 24 24" id="Layer_1" version="1.0" viewBox="0 0 24 24" xml:space="preserve" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink"><circle cx="12" cy="12" r="2"/><circle cx="12" cy="5" r="2"/><circle cx="12" cy="19" r="2"/></svg>
							  </button>
							  <div class="dropdown-menu dropdown-menu-right" aria-labelledby="dropdownMenuLink${job.id}">
								<button class="dropdown-item btn-run" `+ (job.status.running ? "disabled='disabled'" : "" )+` title="Schedule Job for execution">Schedule Run</button>
								<a href="${job.details.action.request.uri}" target="_blank" class="dropdown-item" title="Manually Open and execute Job">Manual Run<a>
							  </div>
							</div>
						</div>
					</div>
					<div class="d-none" id="history-${job.id}">
					  <div class="mt-1 py-2 px-3" style="font-size:16px;">
							<div>
								<div style="font-size:18px;font-weight: 500;" class="text-primary"><span class="text-muted">Schedule:</span> "${job.details.schedule}"
								<span class="float-right">` + (job.status.nextRun ? (`<span class="text-muted">Next Run</span>: ` + new Date(job.status.nextRun).toLocaleString('en-GB')) : "") + `</span></div>
								<small class="text-muted">${job.details.action.request.uri}</small>
							</div>
						</div>
						<div class="row no-gutters">
							<div class="col history-table" style="max-width:360px;"></div>
							<div class="col history-messages" style="max-width: calc(100% - 360px);"></div>
						</div>
					</div>
				</div></div>`;
            }

            html += `</div></div>`;
        };

        html += `</div>`;

        $('#jobs').html(html);

        $('.job-toggle').each((k, obj)=>{
            let ele = $(obj).parents(".dropdown");
            $(obj).click(()=>{
                if(ele.length){
                    $(ele).find(".dropdown-menu").addClass("show");
                }
            });
            $(obj).blur(() => {
                setTimeout(() => {
                    if(ele.length){
                        $(ele).find(".dropdown-menu").removeClass("show");
                    }
                }, 300);
            });
        });
        $('.collection-toggle input').bootstrapToggle()

        //open job history
        $(".display-history").click(async(e)=>{
            let jobid = $(e.target).parents(".job-item").data("jobid");
            let historyEle = $("#history-" + jobid);
            if(historyEle && historyEle.length){


                let historyRepsonse = await scheduleRequest("GET", "/dash/history.php?id=" + jobid);

                let historyTable = "";
                let historyMessages = "";

                historyTable += `<table class="table table-hover table-sm m-0 p-0" style="font-size:14px;">`;

                if(historyRepsonse.history && historyRepsonse.history.length){
                    historyTable += `<thead>
									<tr>
										<th style="width:160px;">Date</th>
										<th style="width:80px;">Duration</th>
										<th style="width:80px">Status</th>
									</tr>
								</thead>
								<tbody>`;

                    for(let i=0; i<historyRepsonse.history.length; i++) {
                        let kk = (i + 1);
                        let history = historyRepsonse.history[i];
                        let message = history.message ? history.message : "no output";

                        console.log(history);

                        let historyClass = (history.status == "PASSING" ? "success" : "danger");

                        let duration = "-";
                        let started = new Date(history.started);
                        let finished = new Date(history.finished);
                        if(started && finished){
                            duration = Math.abs(finished.getTime() - started.getTime());
                            if(duration < 1000){
                                duration = (duration).toFixed(2) + " ms";
                            } else if(duration > 60000){
                                duration = ((duration / 1000) / 60).toFixed(2) + " min";
                            } else {
                                duration = (duration / 1000).toFixed(2) + " sec";
                            }
                        }


                        historyTable += `<tr data-index="${kk}" class="item-clickable">
										<td style="width:160px;">` + started.toLocaleString('en-GB') + `</td>
										<td style="width:80px;">${duration}</td>
										<td style="width:80px"><div class="badge badge-${historyClass}">${history.status}</div></td>
									</tr>`;
                        historyMessages += `<div class="history-item history-${kk} d-none" style="height:100%;">
											<div class="history-output border bg-light">${message}</div>
										</div>`;
                    }
                } else {
                    historyTable += `<tr><td>no history</td></tr>`;
                }
                historyTable += `</tbody></table>`;

                $(historyEle).find('.history-table').html(historyTable);
                $(historyEle).find('.history-messages').html(historyMessages);


                let historyModal = $('#jobHistory')
                historyModal.find(".modal-title").text($(e.target).parents(".job-item").find(".job-title").text() + " - History");
                historyModal.find(".modal-body").html(historyEle.html());

                //click on rows
                $(historyModal).find(".modal-body").find(".table-hover tbody>tr").click((e)=>{
                    $(historyModal).find(".modal-body").find(".table-hover tbody>tr").removeClass("table-active");
                    let index = $(e.target).closest("tr").addClass("table-active").data("index");
                    if(index){
                        $(historyModal).find(".history-item").addClass("d-none");
                        $(historyModal).find(".history-" + index).removeClass("d-none");
                    }
                });

                $(historyModal).find(".modal-body").find(".table-hover tbody>tr:first").click();

                historyModal.modal('show');
            }
        })

        //run job now!
        $(".job-item .btn-run").click(async(e)=>{
            let jobId = $(e.target).parents(".job-item").data("jobid");
            if(jobId){
                let response = await scheduleRequest("PATCH", `/jobs/${jobId}/status`, {running:true});
                if(response.status == 204){
                    $(e.target).prop("disabled", true);
                    loadJobs();
                } else {
                    alert("error running");
                }

            }
        });

        $('.collection-toggle input').change(async(e) => {

            let toggle = $(e.target).prop('checked');
            let toggleDiv = $(e.target).closest('.collection-toggle');
            let col = toggleDiv.data('colid');
            if(col){
                let response = await scheduleRequest("PATCH", `/collections/${col}`, {state: (toggle ? "enabled" : "disabled")});
                if(response.status == 204){
                    //$(e.target).prop("disabled", true);
                    loadJobs();
                } else {
                    //alert("error running");
                }
            }
        });

        console.timeEnd("loadjobs");
        setTimeout(loadJobs, 15000);
    }

    loadJobs();

    (function(window){
        window.htmlentities = {
            encode : function(str) {
                var buf = [];
                for (var i=str.length-1;i>=0;i--) {
                    buf.unshift(['&#', str[i].charCodeAt(), ';'].join(''));
                }
                return buf.join('');
            },
            decode : function(str) {
                return str.replace(/&#(\d+);/g, function(match, dec) {
                    return String.fromCharCode(dec);
                });
            }
        };
    })(window);

</script>