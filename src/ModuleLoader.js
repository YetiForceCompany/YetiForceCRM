/**
 * Module loader
 *
 * @author Rafal Pospiech <r.pospiech@yetiforce.com>
 */

const { lstatSync, readdirSync, writeFileSync, readFileSync } = require('fs');
const { join, resolve } = require('path');


const isDirectory = source => lstatSync(source).isDirectory();
const isFile = source => lstatSync(source).isFile();
const getModuleDirectories = source => readdirSync(source)
  .map(name => join(source, name))
  .filter(isDirectory)
  .map(name => name.substr(source.length + 1));
const getFiles = source => readdirSync(source)
  .map(name => join(source, name))
  .filter(isFile)
  .map(name => name.substr(source.length + 1));

const moduleDir = 'src/modules';

module.exports = {

  /**
   * Load modules
   *
   * @return  {object}  modules structure
   */
  loadModules() {
    const modules = {};
    getModuleDirectories(moduleDir).forEach(moduleName => {
      const moduleConf = modules[moduleName] = {};
      moduleConf.path = `${moduleDir}/${moduleName}`;
      moduleConf.entry = `${moduleConf.path}/${moduleName}.vue`;
      const moduleDirs = getModuleDirectories(moduleConf.path);
      moduleConf.directories = moduleDirs;
      if (moduleDirs.indexOf('router') !== -1) {
        moduleConf.routes = [];
        const routerDir = `${moduleDir}/${moduleName}/router`;
        getFiles(routerDir).forEach(routerFile => {
          const path = `${routerDir}/${routerFile}`;
          const routes = JSON.parse(readFileSync(path, 'utf8'), true);
          routes.forEach(route => {
            moduleConf.routes.push(route);
          });
        });
      }
    });
    writeFileSync(`src/statics/modules.js`, `window.modules = ${JSON.stringify(modules, null, 2)};`);
    return modules;
  },

}
