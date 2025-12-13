console.log('Versions:', process.versions);
if (process.versions.electron) {
    console.log('Running in Electron!');
    const { app } = require('electron');
    console.log('App is:', app);
} else {
    console.log('Running in Node (Not Electron)');
}
