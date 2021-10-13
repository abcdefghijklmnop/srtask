let basicUrl = '/srtask/srtask/public';
let form1 = document.querySelector('#form1');
let form2 = document.querySelector('#form2');
let res1 = document.querySelector('#response1');
let res2 = document.querySelector('#response2');

//if(data.headers.get('Content-Type') === 'application/json'){}
form1["send1JSON"].addEventListener('click',function(event) {
    event.preventDefault();
    let promise = fetch(`${basicUrl}/secret`,{
        method: 'POST',
        //mode: 'cors', // no-cors, *cors, same-origin
        // cache: 'no-cache', // *default, no-cache, reload, force-cache, only-if-cached
        // credentials: 'omit', // include, *same-origin, omit
        // referrerPolicy: 'no-referrer', // no-referrer, *no-referrer-when-downgrade, origin, origin-when-cross-origin, same-origin, strict-origin, strict-origin-when-cross-origin, unsafe-url
        // redirect: 'follow', // manual, *follow, error
        headers: {'Content-Type': 'application/json'},
        body: JSON.stringify({
            'secret':form1["secret"].value,
            'expireAfter':form1["expireAfter"].value,
            'expireAfterViews':form1["expireAfterViews"].value,
        })
    });

    promise.then((data)=>{ 
        return data.text();
    }).then((data)=>{
        res1.innerText = data;
        console.log(data);
    }).catch((error) => {
        console.error('Error:', error);
      });
})



form1["send1XML"].addEventListener('click',function(event) {
    event.preventDefault();
    let promise = fetch(`${basicUrl}/secret`,{
        method: 'POST',
        headers: {'Content-Type': 'application/xml'},
        body:   `<root>
                    <secret>${form1["secret"].value}</secret>
                    <expireAfter>${form1["expireAfter"].value}</expireAfter>
                    <expireAfterViews>${form1["expireAfterViews"].value}</expireAfterViews>
                </root>`
    });

    promise.then((data)=>{ 
        return data.text();
    }).then((data)=>{
        res1.innerText = data;
        console.log(data);
    }).catch((error) => {
        console.error('Error:', error);
      });
})

////////////////////////////////////////////////////////////////////
form2["send2JSON"].addEventListener('click',function(event) {
    event.preventDefault();
    let promise = fetch(`${basicUrl}/secret/${encodeURI(form2['hash'].value)}`,{
        method: 'GET',
        headers: {'Content-Type': 'application/json'},
    });

    promise.then((data)=>{ 
        return data.text();
    }).then((data)=>{
        res2.innerText = data;
        console.log(data);
    }).catch((error) => {
        console.error('Error:', error);
      });
})



form2["send2XML"].addEventListener('click',function(event) {
    event.preventDefault();
    let promise = fetch(`${basicUrl}/secret/${encodeURI(form2['hash'].value)}`,{
        method: 'GET',
        headers: {'Content-Type': 'application/xml'},
    });

    promise.then((data)=>{ 
        return data.text();
    }).then((data)=>{
        res2.innerText = data;
        console.log(data);
    }).catch((error) => {
        console.error('Error:', error);
      });
})