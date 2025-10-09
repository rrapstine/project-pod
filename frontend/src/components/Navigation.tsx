import { Link } from 'react-router-dom';
import { Button } from './ui/button';

export default function Navigation() {
  return (
    <nav className="p-4 border-b">
      <div className="max-w-4xl mx-auto flex gap-4">
        <Link to="/">
          <Button variant="ghost">Dashboard</Button>
        </Link>
        <Link to="/login">
          <Button variant="ghost">Login</Button>
        </Link>
        <Link to="/register">
          <Button variant="ghost">Register</Button>
        </Link>
      </div>
    </nav>
  );
}
