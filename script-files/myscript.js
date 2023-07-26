function downloadFile(){
    var link = document.createElement('a');

    link.href = 'files/Resume - Renz John Magpantay.pdf';
    link.download = 'Resume-Renz-John-Magpantay.pdf';
    link.click();
}


function toggleNavMenu() {
    var navMenu = document.getElementById('navMenu');
    navMenu.style.display = (navMenu.style.display === 'block') ? 'none' : 'block';

}