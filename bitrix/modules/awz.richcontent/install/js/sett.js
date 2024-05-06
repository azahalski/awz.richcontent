(function() {
    'use strict';

    if (!!window.awz_sett){
        return;
    }

    window.awz_sett = {
        loc:{'btn-start':'start','progress':'Progress','finish':'Finish'},
        startApi: function(){
            var parent = this;
            BX('IBLOCK_ID').disabled = false;
            BX('IBLOCK_SETT').disabled = false;
            BX('awz-richcontent-prop').disabled = false;
            var formdata = new FormData(BX('FORMACTION'));
            BX('IBLOCK_ID').disabled = true;
            BX('IBLOCK_SETT').disabled = true;
            BX('awz-richcontent-prop').disabled = true;
            BX.ajax.runAction('awz:richcontent.api.admin.process', {
                data: formdata
            }).then(function (response) {
                parent.showProgress(response['data']);
                BX('awz_cnt_all').value = response['data']['awz_cnt_all'];
                BX('awz_cnt').value = response['data']['awz_cnt'];
                BX('awz_last').value = response['data']['awz_last'];
                BX('IBLOCK_ID').disabled = true;
                BX('IBLOCK_SETT').disabled = true;
                BX('awz-richcontent-prop').disabled = true;
                if(response['data']['awz_last']){
                    setTimeout(function(){
                        return parent.startApi();
                    },2000);
                }else{
                    BX('IBLOCK_ID').disabled = false;
                    BX('IBLOCK_SETT').disabled = false;
                    BX('awz-richcontent-prop').disabled = false;
                    parent._ProgressBar.setColor(BX.UI.ProgressBar.Color.SUCCESS);
                    parent._ProgressBar.setTextBefore(parent['loc']['finish']);
                    parent._ProgressBar.update();
                }
            },function (err) {
                parent._ProgressBar.setColor(BX.UI.ProgressBar.Color.DANGER);
                parent._ProgressBar.setTextBefore(err['errors'][0]['message']);
                parent._ProgressBar.update();
                BX.adjust(BX(parent._ProgressBar_Id), {'html':''});
                BX.append(parent._ProgressBar.getContainer(), BX(parent._ProgressBar_Id));
            });
        },
        loadProperties: function(sel, block_id){
            var value = sel.value;
            var parent = this;

            BX.ajax.runAction('awz:richcontent.api.admin.propList', {
                data: {
                    iblock_id: value,
                    method: 'POST'
                }
            }).then(function (response) {
                parent.addSelProperties(response['data'],block_id);
            },function (err) {
                parent.addSelProperties('error',block_id);
            });

        },
        addSelProperties: function(options, block_id){

            if(typeof options != 'object'){
                BX.adjust(BX(block_id), {'html':'error'});
                return;
            }

            var optionsNode = [];
            var k;
            for(k in options){
                optionsNode.push(BX.create({
                    tag: 'option',
                    props: {
                        value: k,
                    },
                    text: options[k]
                }));
            }
            BX.adjust(BX(block_id), {'html':''});
            BX.append(BX.create({
                tag: 'select',
                props: {
                    className: "",
                    id: "awz-richcontent-prop",
                    name: "IBLOCK_PROP",
                },
                children: optionsNode
            }), BX(block_id));
            this.addProgress(block_id+'-progress');
        },
        addProgress: function(block_id){
            this._ProgressBar = new BX.UI.ProgressBar({
                maxValue: 100,
                value: 0,
                statusType: BX.UI.ProgressBar.Status.COUNTER,
                fill: true,
                column: true,
                textBefore: this.loc['progress']
            });
            this._ProgressBar_Id = block_id;
            BX.adjust(BX(this._ProgressBar_Id), {'html':''});
            BX.append(BX.create({
                tag: 'div',
                props: {
                    className: "",
                    id: this._ProgressBar_Id+'-block'
                },
                children: [
                    BX.create({
                        tag: 'button',
                        props: {
                            className: "ui-btn ui-btn-primary",
                            id: this._ProgressBar_Id+'-btn',
                            type: 'submit'
                        },
                        events: {
                            click: BX.proxy(this.startBar, this)
                        },
                        text: this.loc['btn-start']
                    })
                ]
            }), BX(this._ProgressBar_Id));
        },
        showProgress: function(response){
            if(typeof response == 'object' && this._ProgressBar){
                this._ProgressBar.setValue(response['awz_cnt']);
                this._ProgressBar.setMaxValue(response['awz_cnt_all']);
                this._ProgressBar.update();
            }else{
                BX.adjust(BX(this._ProgressBar_Id), {'html':''});
                BX.append(this._ProgressBar.getContainer(), BX(this._ProgressBar_Id));
            }
        },
        startBar: function(e){
            e.preventDefault();
            BX.remove(e.target);
            this.showProgress();
            this._ProgressBar.setTextBefore(this['loc']['progress']);
            this.startApi();
        },
    };


})();