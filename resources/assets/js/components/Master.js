import React, {Component} from 'react';
import { Switch, Route } from 'react-router-dom';
import Basewrapper from './Basewrapper';

const Master = () => (
  <Switch>
    <Route path='/react/' component={Basewrapper}/>
    <Route path='/react/crm/' component={Crmwrapper}/>
  </Switch>
);

export default Master;