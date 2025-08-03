const idContainers = document.getElementsByClassName("user_ids");
const ids = [];
const updateStatus = [];
const updateRole = [];

for(i = 0; i < idContainers.length; i++){
    ids.push(idContainers[i].innerHTML);
}

for(i = 0; i < ids.length; i++){
    let index = i;

    document.getElementById(`status_${ids[i]}`).addEventListener("click", function() {
        switch(this.innerText){
            case "Active":
                this.innerText = "Banned (pending)";
                updateStatus.push(ids[index]);
                break;
            case "Banned":
                this.innerText = "Active (pending)";
                updateStatus.push(ids[index]);
                break;
            case "Active (pending)":
                this.innerText = "Banned";
                updateStatus.splice(updateStatus.indexOf(ids[index]), 1);
                break;
            case "Banned (pending)":
                this.innerText = "Active";
                updateStatus.splice(updateStatus.indexOf(ids[index]), 1);
                break;
        }
    });
    document.getElementById(`role_${ids[i]}`).addEventListener("click", function() {
        switch(this.innerText){
            case "player":
                this.innerText = "admin (pending)";
                updateRole.push(ids[index]);
                break;
            case "admin":
                this.innerText = "player (pending)";
                updateRole.push(ids[index]);
                break;
            case "player (pending)":
                this.innerText = "admin";
                updateRole.splice(updateRole.indexOf(ids[index]), 1);
                break;
            case "admin (pending)":
                this.innerText = "player";
                updateRole.splice(updateRole.indexOf(ids[index]), 1);
                break;
        }
    });
}

document.getElementById("applyUserChanges").addEventListener("click", () => {
    submitUserChanges();
});

function submitUserChanges(){
    const ban = [];
    const unban = [];
    const admin = [];
    const deadmin = [];

    for(i = 0; i < updateStatus.length; i++){
        let elementStatus = document.getElementById(`status_${updateStatus[i]}`).innerText;
        switch(elementStatus){
            case "Active (pending)":
                unban.push(parseInt(updateStatus[i]));
                break;
            case "Banned (pending)":
                ban.push(parseInt(updateStatus[i]));
                break;
        }
    }

    for(i = 0; i < updateRole.length; i++){
        let elementStatus = document.getElementById(`role_${updateRole[i]}`).innerText;
        switch(elementStatus){
            case "player (pending)":
                deadmin.push(parseInt(updateRole[i]));
                break;
            case "admin (pending)":
                admin.push(parseInt(updateRole[i]));
                break;
        }
    }

    const banInput = '<input type="hidden" name="banList" value="' + JSON.stringify(ban) + '">';
    const unbanInput = '<input type="hidden" name="unbanList" value="' + JSON.stringify(unban) + '">';
    const adminInput = '<input type="hidden" name="adminList" value="' + JSON.stringify(admin) + '">';
    const deadminInput = '<input type="hidden" name="deadminList" value="' + JSON.stringify(deadmin) + '">';

    const form = document.createElement('form');
    form.method = "post";
    form.innerHTML = '<input type="hidden" name="user_changes" value="1">'.concat(banInput, unbanInput, adminInput, deadminInput);

    document.body.appendChild(form);

    form.submit();
}
