import { BrowserRouter as Router, Route, Routes, Link } from 'react-router-dom';
import MenuFrontend from './MenuFrontend';

function Home() {
  return (
    <div style={{ textAlign: 'center', marginTop: '40px' }}>
      <h1>Welcome to the Restaurant App</h1>
      <Link to="/menu/frontend" style={{ fontSize: '20px', color: 'blue' }}>
        View Menu
      </Link>
    </div>
  );
}

function App() {
  return (
    <Router>
      <Routes>
        <Route path="/" element={<Home />} />
        <Route path="/menu/frontend" element={<MenuFrontend />} />
      </Routes>
    </Router>
  );
}

export default App;
