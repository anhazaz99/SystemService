import React, { useEffect, useState } from 'react';
import { useRouter } from 'next/router';
import Layout from '@/components/Layout';
import { useAuth } from '@/contexts/AuthContext';
import { apiClient } from '@/lib/api';
import { CalendarEvent } from '@/types';
import { CalendarDaysIcon } from '@heroicons/react/24/outline';

export default function CalendarPage() {
  const router = useRouter();
  const { isAuthenticated, loading: authLoading } = useAuth();
  const [events, setEvents] = useState<CalendarEvent[]>([]);
  const [loading, setLoading] = useState(true);
  const [range, setRange] = useState<{ start: string; end: string }>(() => {
    const now = new Date();
    const start = new Date(now.getFullYear(), now.getMonth(), 1).toISOString();
    const end = new Date(now.getFullYear(), now.getMonth() + 1, 0, 23, 59, 59).toISOString();
    return { start, end };
  });

  useEffect(() => {
    if (!authLoading && !isAuthenticated) {
      router.push('/login');
      return;
    }
    if (isAuthenticated) {
      loadEvents();
    }
  }, [isAuthenticated, authLoading, router, range]);

  const loadEvents = async () => {
    try {
      setLoading(true);
      const res = await apiClient.getEventsByRange(range.start, range.end);
      setEvents(res.data || []);
    } finally {
      setLoading(false);
    }
  };

  if (authLoading || !isAuthenticated) {
    return (
      <div className="min-h-screen flex items-center justify-center">
        <div className="animate-spin rounded-full h-32 w-32 border-b-2 border-blue-600"></div>
      </div>
    );
  }

  const formatDateTime = (iso: string) => new Date(iso).toLocaleString('vi-VN');

  return (
    <Layout title="Calendar - Task Management">
      <div className="space-y-6">
        <div className="flex items-center justify-between">
          <div>
            <h1 className="text-2xl font-bold text-gray-900">Calendar</h1>
            <p className="text-gray-600">Events in selected range</p>
          </div>
          <div className="flex items-center space-x-2">
            <input
              type="date"
              value={range.start.slice(0, 10)}
              onChange={(e) => {
                const start = new Date(e.target.value);
                const newStart = new Date(Date.UTC(start.getFullYear(), start.getMonth(), start.getDate())).toISOString();
                setRange((r) => ({ ...r, start: newStart }));
              }}
              className="form-input"
            />
            <span className="text-gray-500">to</span>
            <input
              type="date"
              value={range.end.slice(0, 10)}
              onChange={(e) => {
                const end = new Date(e.target.value);
                const newEnd = new Date(Date.UTC(end.getFullYear(), end.getMonth(), end.getDate(), 23, 59, 59)).toISOString();
                setRange((r) => ({ ...r, end: newEnd }));
              }}
              className="form-input"
            />
          </div>
        </div>

        {loading ? (
          <div className="flex items-center justify-center py-12">
            <div className="animate-spin rounded-full h-8 w-8 border-b-2 border-blue-600"></div>
          </div>
        ) : (
          <div className="card">
            {events.length ? (
              <ul className="divide-y divide-gray-200">
                {events.map((e) => (
                  <li key={e.id} className="py-3 flex items-start">
                    <CalendarDaysIcon className="w-5 h-5 text-blue-600 mt-1 mr-3" />
                    <div>
                      <p className="text-sm font-medium text-gray-900">{e.title}</p>
                      <p className="text-xs text-gray-500">{formatDateTime(e.start)}{e.end ? ` → ${formatDateTime(e.end)}` : ''}</p>
                      {e.type && (
                        <span className="inline-block mt-1 text-xs bg-blue-100 text-blue-700 px-2 py-0.5 rounded-full">{e.type}</span>
                      )}
                    </div>
                  </li>
                ))}
              </ul>
            ) : (
              <p className="text-center text-gray-500">No events in this range</p>
            )}
          </div>
        )}
      </div>
    </Layout>
  );
}


