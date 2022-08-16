import React, {Component} from 'react';
import Welcome from './Welcome';
const Basewrapper = () => (
  <div>
    <input type="hidden" id="ip" value="192.168.10.10"/>
      <div className="admin-layout">
        <div className="global-wrapper js-open-menu-wrapper">
          <a className="logo js-open-menu-link" href="#"></a>
          <div className="global-nav-wrapper">
            <nav className="global-nav">
              <div className="global-nav-item">
                <a className="js-nav-link" href="#" data-section="task-section">
                  Пользователи
                </a>
              </div>
              <div className="global-nav-item">
                <a className="js-nav-link" href="#" data-section="task-section">
                  Базы
                </a>
              </div>
              <div className="global-nav-item">
                <a className="js-nav-link" href="#"
                   data-section="task-section">
                  Задачи
                </a>
              </div>
              <div className="global-nav-item">
                <a className="js-nav-link" href="#">
                  Выйти
                </a>
              </div>
            </nav>
          </div>
          <div className="global-container">
            <main className="global-content">
              <section className="global-section task-page current-section js-nav-section" data-section="task-section">
                <Welcome />
              </section>
            </main>
          </div>
          <div className="global-framebox"></div>
        </div>
      </div>
      <img src="/img/loading.gif" className="loading"/>
  </div>
);

export default Basewrapper;