//Some of the variables / functions are declared in view.php as they reference PHP
//Function that gets pages through AJAX (method in controller), and calls method to populate grid
function getPages(formdata){
    console.log("Going to print the recieved topics...")
    
    for(var i = 0; i < formdata.topics.length; i++){
        if(formdata.topics[i] == null || formdata.topics[i] == undefined || formdata.topics[i] == 'null'){
            console.log("null value!")
            formdata.topics[i] = -1
        }
        console.log("Value: " + i)
        console.log(formdata.topics[i])
        console.log("Type: " + typeof formdata.topics[i])
    }
    console.log("Sending topics: ")
    console.log(formdata)
    return $.ajax({
        type        : 'POST',
        dataType    : 'json',
        data        : formdata,
        url         : urlFilterAction,
        encode      : true,
        beforeSend: function(){
            $("#loader").show();
            $('#tspages').empty();
        },
        success: function(response){},
        complete:function(data){
            setTimeout(() => {  $("#loader").hide(); }, 1000);
        },
        error:function(error){
            console.log(error)
            $("#tspages").empty().append(`
                <div>
                    Could not load any pages
                </div>
            `)
        }
    });
}

//Function that fills the grid with the found pages
function fillPageGrid(response, paramsToAddToURL){
    console.log("Results")
    console.log(response.result)
    console.log("Status")
    console.log(response.status)
    console.log("XHR")
    console.log(response.xhr)
    console.log("Params to add to URL")
    console.log(paramsToAddToURL)

    var $pagegrid = $("#tspages").empty()
    var appendingPages = "";
    if(response.status == 'success'){
        //If pages are properly got, we can now replace them!!!
        var counter = 0;
        console.log("Number of pages" + response.result.pagedata.length)

        if(response.result.pagedata.length < 1){
            appendingPages += `
                <div>
                    Could not find any pages!
                </div>
            `
        } else {
            response.result.pagedata.forEach(element => {
                console.log("element")
                var urlGridItem = response.result.pagedata[counter].url 
                if(typeof paramsToAddToURL !== 'undefined'){
                    urlGridItem += '?' 
                    urlGridItem += paramsToAddToURL.toString()
                }
                var currPage = `
                <div id="page-item" class="ts-pl2-grid-item ts-pl2-grid-item-flex">
                    <div class="ts-pl2-header" style="background-color:${response.result.pagedata[counter].color}">
                        ${response.result.pagedata[counter].theme}
                    </div>
                    <div class="ts-pl2-details"><a
                        class="ts-pl2-details-content"
                        href="${urlGridItem}"
                        target="_blank"
                    >
                        ${response.result.pagedata[counter].title}
                    </a></div>
                </div>
                `
                appendingPages += currPage
                counter += 1
            })
        }
    } else {
        appendingPages += `
            <div>
                Error getting pages!
            </div>
        `
    }
    $pagegrid.append(appendingPages)
}

//Sets the topic drop-down menus
function setSelects(values, callingBlock){
    var blocks = []
    var block = $(".topic_select_parent").closest('.topic_select_parent');
    
    blocks = block.find(':input')

    let updatedBlocks = []
    for(var y=0; y<values.length;y++){
        if(!(values[y] == -1)){
            for(var i = 0; i< blocks.length; i++){
                if($("#"+blocks[i].id +" option[value='"+values[y]+"']").length > 0){
                    if(!(updatedBlocks.includes(blocks[i].id))){
                        if(!(values[y] == -1)){
                            updatedBlocks.push(blocks[i].id)
                        }
                        
                        $("#"+blocks[i].id).val(values[y].toString())
                    }
                }
            }
        } else {
            console.log("Value is first index, DO NOT SET")
        }
    }
    updatedBlocks = []
}



$(function() {
    console.log( "View.js ready!" );

    const queryString = window.location.search;
    console.log(queryString);

    const urlParams = new URLSearchParams(queryString);
    let urlTopics = urlParams.getAll('topics[]');
    if(!urlTopics === undefined || !urlTopics.length == 0){
        console.log("Topics defined!")
        setSelects(urlTopics)
        getPages({topics:urlTopics}).then(function(result,status,xhr){
            var pageresponse = {result,status,xhr}
            fillPageGrid(pageresponse, urlParams.toString())
        })
    } else {
        //No topics defined, base search
        console.log("No topics defined!")
        getPages({topics:[]}).then(function(result,status,xhr){
            var pageresponse = {result,status,xhr}
            fillPageGrid(pageresponse, undefined)
        })
    }


    $('#tsreload').click(function(){
        console.log("Clicked reload...")
        getPages({topics:[-1,-1,-1]}).then(function(result,status,xhr){
            var pageresponse = {result,status,xhr}
            fillPageGrid(pageresponse, undefined)
        })
        var blocks = []
        var block = $(this).closest('.topic_select_parent');
        
        blocks = block.find(':input')
        console.log('Num o blocks: ' + blocks.length)
        console.log(blocks)
        for(var i = 0; i < blocks.length; i++){
            console.log(blocks[i])
            console.log(blocks[i].id)
            //$("#"+blocks[i].id).prop('selectedIndex', 0);
            $("#"+blocks[i].id).val("-1");
        }

        //rebuild URL as well, otherwise on page reload will keep the old one, and on subsequent searches as well (unless they are overwritten)
        var selectedCountry = $(this).children("option:selected").val();
        console.log('Event firing selected: ' + selectedCountry)

        var block = $(this).closest('.topic_select_parent');
        
        var blocks = block.find(':input')
        console.log('Num o blocks: ' + blocks.length)
        let values = []
        for(var i=0; i<blocks.length; i++){
            var val = $('#'+blocks[i].id).val()
            values.push(val)
            console.log(val)
        }

        console.log("URL for this page: ")
        console.log(urlForThisPage)
        var filteredURL = urlForThisPage.replace(/(^\w+:|^)\/\//, '');
        filteredURL = filteredURL.substring(filteredURL.indexOf("/"), filteredURL.length)
        filteredURL = filteredURL.split("?")[0]

        filteredURL += "?"

        for(var q=0; q<values.length; q++){
            let localVal = -1
            if((!(typeof values[q] == 'null')) && (!(typeof values[q] == 'undefined')) && (!(values[q] == null))){
                localVal = values[q]
                filteredURL += "topics[]=" + localVal +"&"
            }
        }
        filteredURL = filteredURL.substring(0, filteredURL.length - 1)
        console.log(filteredURL)
        window.history.pushState("object or string", "Page Title", filteredURL);
    })

    //If any of the topic selects change, update the URL and request the pages
	$('.pagelist2[data-action=topic-select2]').change(function() {
        var action = $(this).attr('data-action').replace('--topic--', $(this).val());
        console.log($(this));
        console.log("Link: " + window.location.href);
        console.log("Action: " + action);
        let parts = action.split('/');
        console.log(parts)

        var selectedCountry = $(this). children("option:selected"). val();
        console.log('Event firing selected: ' + selectedCountry)

        var block = $(this).closest('.topic_select_parent');
        
        var blocks = block.find(':input')
        console.log('Num o blocks: ' + blocks.length)
        let values = []
        for(var i=0; i<blocks.length; i++){
            var val = $('#'+blocks[i].id).val()
            values.push(val)
            console.log(val)
        }

        let formData = {
            topics: values
        }

        //Update URL
        console.log("URL for this page: ")
        console.log(urlForThisPage)
        var filteredURL = urlForThisPage.replace(/(^\w+:|^)\/\//, '');
        filteredURL = filteredURL.substring(filteredURL.indexOf("/"), filteredURL.length)
        filteredURL = filteredURL.split("?")[0]

        filteredURL += "?"

        let topicsListToRedirect = []

        for(var q=0; q<values.length; q++){
            let localVal = -1
            if((!(typeof values[q] == 'null')) && (!(typeof values[q] == 'undefined')) && (!(values[q] == null))){
                localVal = values[q]
                topicsListToRedirect.push(localVal)
                filteredURL += "topics[]=" + localVal +"&"
            }
        }

        filteredURL = filteredURL.substring(0, filteredURL.length - 1)
        console.log(filteredURL)

        //Need to get parameters that were set
        const urlParams = new URLSearchParams(queryString);
        topicsListToRedirect.forEach(element => {
            urlParams.append('topics[]', element)
        })

        if(sendToAnotherPage == 1){
            window.location.href = sendToAnotherPageIDURL + '?' + urlParams.toString()
        } else {
            window.history.pushState("object or string", "Page Title", filteredURL);
            
            getPages(formData)
            .then(function(result,status,xhr){
                var pageresponse = {result,status,xhr}
                fillPageGrid(pageresponse, urlParams)
            })
            
            return false;
        }
    });
});