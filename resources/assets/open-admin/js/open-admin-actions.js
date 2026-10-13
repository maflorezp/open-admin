/*-------------------------------------------------*/
/* forms */
/*-------------------------------------------------*/

admin.actions = {


    init : function(){


    },

    add : function(action){

        /*
        admin.actions.add({
            selector : '{$this->selector($this->selectorPrefix)}',
            event :'{$this->event}',
            parameters : '{$parameters}',
            _action: '{$this->getCalledClass()}',
            url : '{$this->getHandleRoute()}',
            method : '{$ajaxMethod}',
            promise : {$this->preScript()}
        })
        */

        document.querySelectorAll(action.selector).forEach(el=>{

            el.addEventListener(action.event,function(){

                var data = el.dataset;
                var target = el;
                Object.assign(data, action.parameters);

                const myPromise = new Promise((resolve, reject) => {
                    action.pre(resolve,reject);
                });

                myPromise.then(function(){

                    Object.assign(data, {
                        _action: action._action
                    });
                    if (data["_key"] === undefined){
                        data._key =admin.grid.selected.join();
                    }

                    var url = action.url;
                    admin.ajax[action.method](url, data, function(data){
                        admin.actions.actionResolver([data,el]);
                    }, function(error){
                        admin.actions.actionCatcher(error);
                    });
                }).catch(function(){
                    console.log("canceled")
                });

            });
        });
    },

    actionResolver : function (data) {

        var response = data[0].data;
        var target   = data[1];
        if (typeof response === 'string') {
            target.innerHTML = response;
        }else if (typeof response !== 'object') {

            Swal.fire({type: 'error', title: 'Oops!'});
            console.log(response);
        }

        var then = function (then) {
            if (then.action == 'refresh') {
                admin.ajax.reload();
            }

            if (then.action == 'download') {
                window.open(then.value, '_blank');
            }

            if (then.action == 'redirect') {
                admin.ajax.navigate(then.value);
            }

            if (then.action == 'location') {
                window.location = then.value;
            }

            if (then.action == 'open') {
                window.open(then.value, '_blank');
            }
        };

        if (typeof response.html === 'string') {
            target.innerHTML = response.html;
        }

        if (typeof response.swal === 'object') {
            Swal.fire(response.swal);
        }

        if (typeof response.toastr === 'object' && response.toastr.type) {
            admin.toastr[response.toastr.type](response.toastr.content, response.toastr.options);
        }

        if (response.then) {
          then(response.then);
        }
    },

    // Muestra por qué falló una acción. Antes nunca se llamaba y estaba escrito para el toastr de
    // jQuery que el panel ya no usa: un 500, un token vencido o una caída de red pasaban en silencio
    actionCatcher : function (error) {
        var response = error ? error.response : null;
        var data = response ? response.data : null;
        var message = null;

        if (data) {
            if (typeof data === 'string') {
                message = data;
            } else if (data.toastr && data.toastr.content) {
                message = data.toastr.content;
            } else if (data.message) {
                message = data.message;
            } else if (data.errors) {
                message = Object.keys(data.errors).map(function (key) {
                    return [].concat(data.errors[key]).join('\n');
                }).join('\n');
            }
        }

        if (!message) {
            message = response ? 'Request failed (' + response.status + ')' : 'Request failed';
        }

        admin.toastr.error(message, {duration: 10000});
        console.log(error);
    }
}