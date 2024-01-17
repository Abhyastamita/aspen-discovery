import * as Device from 'expo-device';
import _ from 'lodash';
import React, { useState } from 'react';
import { getTermFromDictionary } from '../translations/TranslationService';
import { BRANCH, formatDiscoveryVersion } from '../util/loadLibrary';
import { PATRON } from '../util/loadPatron';

export const ThemeContext = React.createContext({
     theme: [],
     updateTheme: () => {},
     resetTheme: () => {},
});
export const DiscoveryContext = React.createContext();
export const UserContext = React.createContext({
     updateUser: () => {},
     user: [],
     updateLinkedAccounts: () => {},
     accounts: [],
     updateLists: () => {},
     lists: [],
     updateLanguage: () => {},
     language: [],
     updatePickupLocations: () => {},
     locations: [],
     cards: [],
     updateCards: () => {},
     notificationSettings: [],
     updateNotificationSettings: () => {},
     expoToken: false,
     aspenToken: false,
     resetUser: () => {},
     updateExpoToken: () => {},
     updateAspenToken: () => {},
     notificationOnboard: 0,
     updateNotificationOnboard: () => {},
     notificationOnboardStatus: false,
     updateNotificationOnboardStatus: () => {},
});
export const LibrarySystemContext = React.createContext({
     updateLibrary: () => {},
     library: [],
     version: '',
     url: '',
     menu: [],
     updateMenu: () => {},
     resetLibrary: () => {},
});
export const LibraryBranchContext = React.createContext({
     updateLocation: () => {},
     location: [],
     resetLocation: () => {},
     scope: '',
     updateScope: () => {},
     enableSelfCheck: false,
     updateEnableSelfCheck: () => {},
     selfCheckSettings: [],
     updateSelfCheckSettings: () => {},
});
export const BrowseCategoryContext = React.createContext({
     updateBrowseCategories: () => {},
     category: [],
     updateBrowseCategoryList: () => {},
     list: [],
     updateMaxCategories: () => {},
     maxNum: 5,
     resetBrowseCategories: () => {},
});
export const CheckoutsContext = React.createContext({
     updateCheckouts: () => {},
     checkouts: [],
     resetCheckouts: () => {},
});
export const HoldsContext = React.createContext({
     updateHolds: () => {},
     holds: [],
     resetHolds: () => {},
     pendingSortMethod: 'sortTitle',
     readySortMethod: 'expire',
     updatePendingSortMethod: () => {},
     updateReadySortMethod: () => {},
});
export const GroupedWorkContext = React.createContext({
     updateGroupedWork: () => {},
     updateFormat: () => {},
     updateLanguage: () => {},
     groupedWork: [],
     format: [],
     language: [],
     resetGroupedWork: () => {},
});
export const LanguageContext = React.createContext({
     updateLanguage: () => {},
     language: '',
     languages: [],
     dictionary: [],
     updateLanguages: () => {},
     updateDictionary: () => {},
     resetLanguage: () => {},
});
export const SystemMessagesContext = React.createContext({
     updateSystemMessages: () => {},
     systemMessages: [],
     resetSystemMessages: () => {},
});

export const SearchContext = React.createContext({
     query: '',
     currentIndex: 'Keyword',
     currentSource: 'local',
     sources: [],
     indexes: [],
     facets: [],
     sort: 'relevance',
     updateQuery: () => {},
     updateCurrentIndex: () => {},
     updateCurrentSource: () => {},
     updateIndexes: () => {},
     updateSources: () => {},
     updateFacets: () => {},
     updateSort: () => {},
     resetSearch: () => {},
});

export const ThemeProvider = ({ children }) => {
     const [theme, setTheme] = useState([]);

     const updateTheme = (data) => {
          setTheme(data);
     };

     const resetTheme = () => {
          setTheme([]);
     };

     return (
          <ThemeContext.Provider
               value={{
                    theme,
                    updateTheme,
                    resetTheme,
               }}>
               {children}
          </ThemeContext.Provider>
     );
};

export const DiscoveryProvider = ({ children }) => {
     const [version, setVersion] = useState();
     const [url, setUrl] = useState();

     const updateVersion = (data) => {
          const thisVersion = formatDiscoveryVersion(data);
          setVersion(thisVersion);
     };

     const updateUrl = (data) => {
          setUrl(data);
     };

     return (
          <DiscoveryContext.Provider
               value={{
                    version,
                    url,
                    updateVersion,
                    updateUrl,
               }}>
               {children}
          </DiscoveryContext.Provider>
     );
};

export const LibrarySystemProvider = ({ children }) => {
     const [library, setLibrary] = useState();
     const [version, setVersion] = useState();
     const [url, setUrl] = useState();
     const [menu, setMenu] = useState();

     const updateLibrary = (data) => {
          if (!_.isUndefined(data.discoveryVersion)) {
               const discovery = formatDiscoveryVersion(data.discoveryVersion);
               setVersion(discovery);
               console.log('updated version in LibrarySystemContext');
          }

          if (!_.isUndefined(data.baseUrl)) {
               setUrl(data.baseUrl);
               console.log('updated url in LibrarySystemContext');
          }
          setLibrary(data);
          console.log('updated LibrarySystemContext');
     };

     const resetLibrary = () => {
          setLibrary({});
          setVersion({});
          setUrl({});
          setMenu({});
          console.log('reset LibrarySystemContext');
     };

     const updateMenu = (data) => {
          setMenu(data);
          console.log('updated menu in LibrarySystemContext');
     };

     return (
          <LibrarySystemContext.Provider
               value={{
                    library,
                    version,
                    url,
                    updateLibrary,
                    resetLibrary,
                    menu,
                    updateMenu,
               }}>
               {children}
          </LibrarySystemContext.Provider>
     );
};

export const LibraryBranchProvider = ({ children }) => {
     const [location, setLocation] = useState();
     const [scope, setScope] = useState();
     const [enableSelfCheck, setEnableSelfCheck] = useState(false);
     const [selfCheckSettings, setSelfCheckSettings] = useState([]);

     const updateLocation = (data) => {
          setLocation(data);

          if (!_.isUndefined(data.vdxFormId)) {
               BRANCH.vdxFormId = data.vdxFormId;
          }

          if (!_.isUndefined(data.vdxLocation)) {
               BRANCH.vdxLocation = data.vdxLocation;
          }

          console.log('updated LibraryBranchContext');
     };

     const updateScope = (data) => {
          setScope(data);
          console.log('updated scope in LibraryBranchContext');
     };

     const resetLocation = () => {
          setLocation({});
          setScope({});
          console.log('reset LibraryBranchContext');
     };

     const updateEnableSelfCheck = (status) => {
          setEnableSelfCheck(status);
          console.log('updated self check in LibraryBranchContext');
     };

     const updateSelfCheckSettings = (data) => {
          setSelfCheckSettings(data);
          console.log('updated self check settings in LibraryBranchContext');
     };

     return (
          <LibraryBranchContext.Provider
               value={{
                    location,
                    scope,
                    enableSelfCheck,
                    selfCheckSettings,
                    updateLocation,
                    resetLocation,
                    updateScope,
                    updateEnableSelfCheck,
                    updateSelfCheckSettings,
               }}>
               {children}
          </LibraryBranchContext.Provider>
     );
};

export const UserProvider = ({ children }) => {
     const [user, setUser] = useState([]);
     const [accounts, setLinkedAccounts] = useState([]);
     const [viewers, setLinkedViewerAccounts] = useState([]);
     const [lists, setLists] = useState([]);
     const [language, setLanguage] = useState('en');
     const [locations, setPickupLocations] = useState([]);
     const [readingHistory, setReadingHistory] = useState([]);
     const [cards, setCards] = useState([]);
     const [notificationSettings, setNotificationSettings] = useState([]);
     const [notificationOnboard, setNotificationOnboard] = useState(0);
     const [expoToken, setExpoToken] = useState(false);
     const [aspenToken, setAspenToken] = useState(false);

     const updateUser = (data) => {
          if (_.isObject(data) && !_.isUndefined(data.lastListUsed)) {
               PATRON.listLastUsed = data.lastListUsed;
          }

          if (_.isObject(data) && !_.isUndefined(data.numHolds)) {
               PATRON.num.holds = data.numHolds;
          }

          if (_.isObject(data) && !_.isUndefined(data.notification_preferences)) {
               updateNotificationSettings(data.notification_preferences, data.interfaceLanguage ?? 'en', data.onboardAppNotifications);
          }

          if (_.isObject(data) && !_.isUndefined(data.onboardAppNotifications)) {
               updateNotificationOnboard(data.onboardAppNotifications);
          }

          setUser(data);
          console.log('updated UserContext');
     };

     const resetUser = () => {
          setUser({});
          setLists({});
          setLinkedAccounts({});
          setLanguage({});
          console.log('reset UserContext');
     };

     const updateLists = (data) => {
          setLists(data);
          console.log('updated lists in UserContext');
     };

     const updateLinkedAccounts = (data) => {
          setLinkedAccounts(data);
          console.log('updated linked accounts in UserContext');
     };

     const updateLinkedViewerAccounts = (data) => {
          setLinkedViewerAccounts(data);
          console.log('updated linked viewer accounts in UserContext');
     };

     const updateLanguage = (data) => {
          setLanguage(data);
          console.log('updated language in UserContext');
     };

     const updatePickupLocations = (data) => {
          setPickupLocations(data);
          console.log('updated pickup locations in UserContext');
     };

     const updateReadingHistory = (data) => {
          setReadingHistory(data);
          console.log('updated reading history in UserContext');
     };

     const updateLibraryCards = (data) => {
          setCards(data);
          console.log('updated library cards in UserContext');
     };

     const updateNotificationSettings = async (data, language, userOnboardStatus) => {
          if (Device.isDevice) {
               if (!_.isEmpty(data)) {
                    const device = Device.modelName;
                    if (_.find(data, _.matchesProperty('device', device))) {
                         console.log('Found settings for this device model');
                         const deviceSettings = _.filter(data, { device: device });
                         const savedSearches = await getTermFromDictionary(language, 'saved_searches');
                         const alertsFromLibrary = await getTermFromDictionary(language, 'alerts_from_library');
                         const alertsAboutAccount = await getTermFromDictionary(language, 'alerts_about_account');
                         const settings = [];
                         settings.push(
                              {
                                   id: 0,
                                   label: savedSearches,
                                   option: 'notifySavedSearch',
                                   description: null,
                                   allow: deviceSettings[0].notifySavedSearch ?? 0,
                              },
                              {
                                   id: 1,
                                   label: alertsFromLibrary,
                                   option: 'notifyCustom',
                                   description: null,
                                   allow: deviceSettings[0].notifyCustom ?? 0,
                              },
                              {
                                   id: 2,
                                   label: alertsAboutAccount,
                                   option: 'notifyAccount',
                                   description: null,
                                   allow: deviceSettings[0].notifyAccount ?? 0,
                              }
                         );
                         setNotificationSettings(settings);
                         setExpoToken(deviceSettings[0]?.token ?? false);
                         setAspenToken(true);

                         if (deviceSettings && _.isObject(deviceSettings)) {
                              if (!_.isUndefined(deviceSettings[0].onboardStatus)) {
                                   setNotificationOnboard(deviceSettings[0].onboardStatus);
                              }
                         } else {
                              // probably connecting to a Discovery version earlier than 23.07.00
                              setNotificationOnboard(0);
                         }
                    } else {
                         console.log('No settings found for this device model yet');
                         setExpoToken(false);
                         setAspenToken(false);

                         // let's not intentionally bombard the user with prompts at every boot without knowing their preferences
                         setNotificationOnboard(0);

                         const deviceSettings = _.filter(data, { device: 'Unknown' });
                         if (deviceSettings && _.isObject(deviceSettings)) {
                              if (!_.isUndefined(deviceSettings[0].onboardStatus)) {
                                   setNotificationOnboard(deviceSettings[0].onboardStatus);
                              }
                         }

                         if (userOnboardStatus) {
                              setNotificationOnboard(userOnboardStatus);
                         }
                    }
               } else {
                    // something went wrong when receiving data from Discovery API
                    setExpoToken(false);
                    setAspenToken(false);
                    setNotificationOnboard(0);
               }
          } else {
               setExpoToken(false);
               setAspenToken(false);

               // this is not a device that can use notifications, ignore
               setNotificationOnboard(0);
          }
          //maybe set allowNotifications at this point for initial load?
          console.log('updated notification settings in UserContext');
     };

     const updateExpoToken = (data) => {
          setExpoToken(data);
          console.log('updated expo token UserContext');
     };

     const updateAspenToken = (data) => {
          setAspenToken(data);
          console.log('updated aspen token UserContext');
     };

     const updateNotificationOnboard = (data) => {
          setNotificationOnboard(data);
          console.log('updated notification onboard status in UserContext');
     };

     return (
          <UserContext.Provider
               value={{
                    user,
                    updateUser,
                    resetUser,
                    lists,
                    updateLists,
                    accounts,
                    updateLinkedAccounts,
                    viewers,
                    updateLinkedViewerAccounts,
                    language,
                    updateLanguage,
                    locations,
                    updatePickupLocations,
                    readingHistory,
                    updateReadingHistory,
                    cards,
                    updateLibraryCards,
                    notificationSettings,
                    updateNotificationSettings,
                    expoToken,
                    aspenToken,
                    updateExpoToken,
                    updateAspenToken,
                    notificationOnboard,
                    updateNotificationOnboard,
               }}>
               {children}
          </UserContext.Provider>
     );
};

export const BrowseCategoryProvider = ({ children }) => {
     const [category, setCategories] = useState();
     const [list, setCategoryList] = useState();
     const [maxNum, setMaxCategories] = useState();

     const updateBrowseCategories = (data) => {
          setCategories(data);
          console.log('updated BrowseCategoryContext');
     };

     const updateBrowseCategoryList = (data) => {
          setCategoryList(data);
          console.log('updated list in BrowseCategoryContext');
     };

     const updateMaxCategories = (data) => {
          setMaxCategories(data);
          console.log('updated max categories in BrowseCategoryContext');
     };

     const resetBrowseCategories = () => {
          setCategories({});
          setCategoryList({});
          console.log('reset BrowseCategoryContext');
     };

     return (
          <BrowseCategoryContext.Provider
               value={{
                    category,
                    list,
                    maxNum,
                    updateBrowseCategories,
                    updateBrowseCategoryList,
                    updateMaxCategories,
                    resetBrowseCategories,
               }}>
               {children}
          </BrowseCategoryContext.Provider>
     );
};

export const CheckoutsProvider = ({ children }) => {
     const [checkouts, setCheckouts] = useState();

     const updateCheckouts = (data) => {
          setCheckouts(data);
          console.log('updated CheckoutsContext');
     };

     const resetCheckouts = () => {
          setCheckouts({});
          console.log('reset CheckoutsContext');
     };

     return (
          <CheckoutsContext.Provider
               value={{
                    checkouts,
                    updateCheckouts,
                    resetCheckouts,
               }}>
               {children}
          </CheckoutsContext.Provider>
     );
};

export const HoldsProvider = ({ children }) => {
     const [holds, setHolds] = useState();
     const [pendingSortMethod, setPendingSortMethod] = useState('sortTitle');
     const [readySortMethod, setReadySortMethod] = useState('expire');

     const updateHolds = (data) => {
          setHolds(data);
          console.log('updated HoldsContext');
     };

     const updatePendingSortMethod = (data) => {
          setPendingSortMethod(data);
          console.log('updated pendingSortMethod');
     };

     const updateReadySortMethod = (data) => {
          setReadySortMethod(data);
          console.log('updated readySortMethod');
     };

     const resetHolds = () => {
          setHolds({});
          console.log('reset HoldsContext');
     };

     return (
          <HoldsContext.Provider
               value={{
                    holds,
                    updateHolds,
                    resetHolds,
                    readySortMethod,
                    pendingSortMethod,
                    updateReadySortMethod,
                    updatePendingSortMethod,
               }}>
               {children}
          </HoldsContext.Provider>
     );
};

export const GroupedWorkProvider = ({ children }) => {
     const [groupedWork, setGroupedWork] = useState();
     const [format, setFormat] = useState();
     const [language, setLanguage] = useState();

     const updateGroupedWork = (data) => {
          setGroupedWork(data);
          console.log('updated GroupedWorkContext');

          const keys = _.keys(data.formats);
          setFormat(_.first(keys));
          console.log('updated format in GroupedWorkContext');

          setLanguage(data.language);
          console.log('updated language in GroupedWorkContext');
     };

     const updateFormat = (data) => {
          setFormat(data);
          console.log('updated format in GroupedWorkContext');
     };

     const updateLanguage = (data) => {
          setLanguage(data);
          console.log('updated language in GroupedWorkContext');
     };

     const resetGroupedWork = () => {
          setGroupedWork([]);
          console.log('reset GroupedWorkContext');
     };

     return <GroupedWorkContext.Provider value={{ groupedWork, format, language, updateGroupedWork, updateFormat, updateLanguage, resetGroupedWork }}>{children}</GroupedWorkContext.Provider>;
};

export const LanguageProvider = ({ children }) => {
     const [language, setLanguage] = useState();
     const [languages, setLanguages] = useState();
     const [dictionary, setDictionary] = useState();

     const updateLanguage = (data) => {
          console.log('updated language to ' + data + ' in LanguageContext');
          PATRON.language = data;
          setLanguage(data);
     };

     const updateLanguages = (data) => {
          console.log('updated available library languages in LanguageContext');
          setLanguages(data);
     };

     const updateDictionary = (data) => {
          console.log('updated dictionary in LanguageContext');
          setDictionary(data);
     };

     return (
          <LanguageContext.Provider
               value={{
                    language,
                    updateLanguage,
                    languages,
                    updateLanguages,
                    dictionary,
                    updateDictionary,
               }}>
               {children}
          </LanguageContext.Provider>
     );
};

export const SystemMessagesProvider = ({ children }) => {
     const [systemMessages, setSystemMessages] = useState();

     const updateSystemMessages = (data) => {
          setSystemMessages(data);
          console.log('updated SystemMessagesContext');
     };

     const resetSystemMessages = () => {
          setSystemMessages({});
          console.log('reset SystemMessagesContext');
     };

     return (
          <SystemMessagesContext.Provider
               value={{
                    systemMessages,
                    updateSystemMessages,
                    resetSystemMessages,
               }}>
               {children}
          </SystemMessagesContext.Provider>
     );
};

export const SearchProvider = ({ children }) => {
     const [currentIndex, setCurrentIndex] = useState();
     const [currentSource, setCurrentSource] = useState();
     const [indexes, setIndexes] = useState();
     const [sources, setSources] = useState();
     const [facets, setFacets] = useState();
     const [sort, setSort] = useState();
     const [query, setQuery] = useState();

     const updateCurrentIndex = (data) => {
          setCurrentIndex(data);
          console.log('updated currentIndex in SearchContext');
     };

     const updateCurrentSource = (data) => {
          setCurrentSource(data);
          console.log('updated currentSource in SearchContext');
     };

     const updateIndexes = (data) => {
          setIndexes(data);
          console.log('updated indexes in SearchContext');
     };

     const updateSources = (data) => {
          setSources(data);
          console.log('updated sources in SearchContext');
     };

     const updateFacets = (data) => {
          setFacets(data);
          console.log('updated facets in SearchContext');
     };

     const updateSort = (data) => {
          setSort(data);
          console.log('updated sort in SearchContext');
     };

     const updateQuery = (data) => {
          setQuery(data);
          console.log('updated query in SearchContext');
     };

     const resetSearch = () => {
          setCurrentIndex('Keyword');
          setCurrentSource('local');
          setIndexes({});
          setSources({});
          setQuery('');
          setFacets({});
          setSort('relevance');
          console.log('reset SearchContext');
     };

     return (
          <SearchContext.Provider
               value={{
                    currentIndex,
                    updateCurrentIndex,
                    currentSource,
                    updateCurrentSource,
                    indexes,
                    updateIndexes,
                    sources,
                    updateSources,
                    facets,
                    updateFacets,
                    query,
                    updateQuery,
                    sort,
                    updateSort,
                    resetSearch,
               }}>
               {children}
          </SearchContext.Provider>
     );
};